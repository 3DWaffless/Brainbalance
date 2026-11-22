<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    protected function activeDisk(): string
    {
        return config('filesystems.default') === 'supabase' ? 'supabase' : 'public';
    }

    protected function resolveDiskForPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $primary = $this->activeDisk();

        try {
            if (Storage::disk($primary)->fileExists($path)) {
                return $primary;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                "Storage check failed on [{$primary}] for [{$path}]: " . ($e->getPrevious()?->getMessage() ?? $e->getMessage())
            );
        }

        if ($primary !== 'public') {
            try {
                if (Storage::disk('public')->fileExists($path)) {
                    return 'public';
                }
            } catch (\Throwable $e) {
                // Ignore local fallback check errors
            }
        }

        return null;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!Schema::hasColumn('announcements', 'type')) {
            return view('assignments.index', [
                'assignments' => collect(),
                'filter'      => 'todo',
                'counts'      => ['todo' => 0, 'overdue' => 0, 'completed' => 0, 'all' => 0],
            ]);
        }

        if ($user->isStudent()) {
            $classIds = $user->joinedClasses()->pluck('classes.id');
        } elseif ($user->isTeacher()) {
            $classIds = $user->ownedClasses()->pluck('id');
        } else {
            $classIds = \App\Models\Classroom::pluck('id');
        }

        $allAssignments = Announcement::with(['classroom', 'teacher', 'submissions' => function ($q) use ($user) {
                if ($user->isStudent()) {
                    $q->where('student_id', $user->id);
                } else {
                    $q->with('student');
                }
            }])
            ->whereIn('class_id', $classIds)
            ->where('type', 'assignment')
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('due_at', 'asc')
            ->get();

        $counts = [
            'all'       => $allAssignments->count(),
            'completed' => 0,
            'overdue'   => 0,
            'todo'      => 0,
        ];

        if ($user->isStudent()) {
            foreach ($allAssignments as $item) {
                $isDone = $item->submissions->isNotEmpty();
                $isPast = $item->due_at && $item->due_at->isPast();

                if ($isDone) {
                    $counts['completed']++;
                } elseif ($isPast) {
                    $counts['overdue']++;
                } else {
                    $counts['todo']++;
                }
            }
        } else {
            $counts['todo'] = $allAssignments->count();
        }

        $filter = $request->query('filter', $user->isStudent() ? 'todo' : 'all');

        $assignments = $allAssignments->filter(function ($item) use ($user, $filter) {
            if (!$user->isStudent() || $filter === 'all') {
                return true;
            }
            $isDone = $item->submissions->isNotEmpty();
            $isPast = $item->due_at && $item->due_at->isPast();

            return match ($filter) {
                'completed' => $isDone,
                'overdue'   => !$isDone && $isPast,
                default     => !$isDone && !$isPast,
            };
        })->values();

        return view('assignments.index', compact('assignments', 'filter', 'counts'));
    }

    public function submit(Request $request, Announcement $announcement)
    {
        $user = Auth::user();
        abort_unless($user->isStudent(), 403);
        abort_unless($announcement->type === 'assignment', 404);
        abort_unless($user->joinedClasses->contains($announcement->class_id), 403);

        $request->validate([
            'answer_text' => 'nullable|string|max:5000',
            'file'        => 'nullable|file|max:51200',
        ]);

        if (empty(trim((string) $request->answer_text)) && !$request->hasFile('file')) {
            return back()->with('error', 'Please type your answer or attach a file before turning in.');
        }

        $existing = AssignmentSubmission::where('announcement_id', $announcement->id)
            ->where('student_id', $user->id)
            ->first();

        $filePath = $existing?->file_path;
        $fileName = $existing?->file_name;

        if ($request->hasFile('file')) {
            $oldDisk = $this->resolveDiskForPath($filePath);
            if ($oldDisk) {
                Storage::disk($oldDisk)->delete($filePath);
            }
            $file = $request->file('file');
            $filePath = $file->store('assignment_submissions', $this->activeDisk());
            $fileName = $file->getClientOriginalName();
        }

        AssignmentSubmission::updateOrCreate(
            [
                'announcement_id' => $announcement->id,
                'student_id'      => $user->id,
            ],
            [
                'answer_text'  => trim((string) $request->answer_text),
                'file_path'    => $filePath,
                'file_name'    => $fileName,
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', '🎉 Assignment turned in! Great job!');
    }

    public function grade(Request $request, AssignmentSubmission $submission)
    {
        $user = Auth::user();
        $assignment = $submission->assignment;
        abort_unless($assignment && (($user->isTeacher() && $assignment->teacher_id === $user->id) || $user->role === 'admin'), 403);

        $maxPoints = $assignment->points ?? 100;
        $validated = $request->validate([
            'score'            => "required|integer|min:0|max:{$maxPoints}",
            'teacher_feedback' => 'nullable|string|max:2000',
        ]);

        $submission->update([
            'score'            => $validated['score'],
            'teacher_feedback' => $validated['teacher_feedback'] ?? null,
        ]);

        return back()->with('success', 'Grade and feedback saved!');
    }

    public function downloadSubmission(AssignmentSubmission $submission)
    {
        $user = Auth::user();
        $assignment = $submission->assignment;
        abort_unless($assignment && $submission->file_path, 404);

        $canAccess = $user->role === 'admin'
            || ($user->isStudent() && $submission->student_id === $user->id)
            || ($user->isTeacher() && $user->ownedClasses->contains($assignment->class_id));

        abort_unless($canAccess, 403);

        $disk = $this->resolveDiskForPath($submission->file_path);
        abort_unless($disk, 404);

        return Storage::disk($disk)->download($submission->file_path, $submission->file_name ?? 'submission');
    }
}