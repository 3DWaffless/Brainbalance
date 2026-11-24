<?php
// FILE: app/Http/Controllers/StudentLogController.php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\QuizAttempt;
use App\Models\StudentActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class StudentLogController extends Controller
{
    protected function authorizeTeacher(): void
    {
        $user = auth()->user();

        if (!$user || !in_array($user->role, ['teacher', 'admin'], true)) {
            abort(403, 'Only teachers can access student activity logs.');
        }
    }

    protected function getTeacherScope(): array
    {
        $user = auth()->user();

        if (!Schema::hasTable('classes')) {
            return [collect(), [], collect(), []];
        }

        $classQuery = Classroom::orderBy('name');
        if ($user->role !== 'admin') {
            $classQuery->where('teacher_id', $user->id);
        }

        $classrooms    = $classQuery->get();
        $ownedClassIds = $classrooms->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (empty($ownedClassIds) || !Schema::hasTable('class_student')) {
            return [$classrooms, $ownedClassIds, collect(), []];
        }

        $studentFk = Schema::hasColumn('class_student', 'user_id') ? 'user_id' : 'student_id';
        $classFk   = Schema::hasColumn('class_student', 'class_id') ? 'class_id' : 'classroom_id';

        $enrolledStudentIds = DB::table('class_student')
            ->whereIn($classFk, $ownedClassIds)
            ->pluck($studentFk)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $students = empty($enrolledStudentIds)
            ? collect()
            : User::where('role', 'student')
                ->whereIn('id', $enrolledStudentIds)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

        return [$classrooms, $ownedClassIds, $students, $enrolledStudentIds];
    }

    public function index(Request $request)
    {
        $this->authorizeTeacher();

        [$classrooms, $ownedClassIds, $students] = $this->getTeacherScope();
        $allLogs = $this->buildUnifiedStudentLogs($classrooms, $ownedClassIds, $students, $request);

        $perPage = 20;
        $page    = max(1, (int) $request->input('page', 1));
        $items   = $allLogs->forPage($page, $perPage)->values();

        $logs = new LengthAwarePaginator(
            $items,
            $allLogs->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = [
            'total_students'    => $students->count(),
            'active_today'      => $allLogs->filter(fn ($row) => $row['timestamp'] && $row['timestamp']->isToday())->pluck('student_id')->unique()->count(),
            'total_quizzes'     => $allLogs->where('category', 'quiz')->where('action', 'Completed Quiz')->count(),
            'total_assignments' => $allLogs->where('category', 'assignment')->count(),
            'total_files'       => $allLogs->where('category', 'file')->count(),
            'total_logs'        => $allLogs->count(),
        ];

        return view('teacher.student-logs', compact('logs', 'students', 'classrooms', 'stats'));
    }

    public function export(Request $request)
    {
        $this->authorizeTeacher();

        [$classrooms, $ownedClassIds, $students] = $this->getTeacherScope();
        $allLogs = $this->buildUnifiedStudentLogs($classrooms, $ownedClassIds, $students, $request);

        $filename = 'brainbalance_class_student_logs_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($allLogs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Timestamp', 'Classroom', 'Student Name', 'Student Email', 'Category', 'Action', 'Description', 'Details']);

            foreach ($allLogs as $log) {
                fputcsv($handle, [
                    $log['timestamp'] ? $log['timestamp']->format('Y-m-d H:i:s') : '',
                    $log['classroom_name'] ?? '',
                    $log['student_name'],
                    $log['student_email'],
                    strtoupper($log['category']),
                    $log['action'],
                    $log['description'],
                    $log['detail_badge'] ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function buildUnifiedStudentLogs(Collection $classrooms, array $ownedClassIds, Collection $students, Request $request): Collection
    {
        if (empty($ownedClassIds) || $students->isEmpty() || !Schema::hasTable('class_student')) {
            return collect();
        }

        $classMap       = $classrooms->keyBy('id');
        $targetClassIds = $ownedClassIds;

        // If teacher filters by a specific classroom, ensure it belongs to this teacher
        if ($request->filled('classroom_id')) {
            $requestedClassId = (int) $request->input('classroom_id');
            $targetClassIds   = in_array($requestedClassId, $ownedClassIds, true) ? [$requestedClassId] : [];
        }

        if (empty($targetClassIds)) {
            return collect();
        }

        $studentFk = Schema::hasColumn('class_student', 'user_id') ? 'user_id' : 'student_id';
        $classFk   = Schema::hasColumn('class_student', 'class_id') ? 'class_id' : 'classroom_id';

        // Map each student to the exact target classes they are enrolled in
        $enrollments = DB::table('class_student')
            ->whereIn($classFk, $targetClassIds)
            ->get([$studentFk, $classFk]);

        $enrolledClassIdsByStudent = [];
        foreach ($enrollments as $row) {
            $sid = (int) $row->{$studentFk};
            $cid = (int) $row->{$classFk};
            $enrolledClassIdsByStudent[$sid][] = $cid;
        }

        $filteredStudents = $students->filter(fn ($s) => isset($enrolledClassIdsByStudent[(int) $s->id]));
        $studentMap       = $filteredStudents->keyBy('id');
        $studentIds       = $filteredStudents->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (empty($studentIds)) {
            return collect();
        }

        $entries              = collect();
        $loggedSubmissionKeys = [];

        // 1. Class-scoped StudentActivityLog records (opening files, starting quizzes, assignment submissions)
        if (Schema::hasTable('student_activity_logs')) {
            $hasClassCol = Schema::hasColumn('student_activity_logs', 'class_id');

            $logQuery = StudentActivityLog::whereIn('user_id', $studentIds)
                ->whereIn('category', ['file', 'assignment', 'quiz'])
                ->latest()
                ->limit(600);

            if ($hasClassCol) {
                $logQuery->whereIn('class_id', $targetClassIds);
            }

            foreach ($logQuery->get() as $log) {
                $sid     = (int) $log->user_id;
                $student = $studentMap->get($sid);
                if (!$student) {
                    continue;
                }

                $meta    = is_array($log->metadata) ? $log->metadata : [];
                $classId = (int) ($log->class_id ?? ($meta['class_id'] ?? 0));

                // Strictly require the activity to belong to the teacher's target class AND a class the student is in
                if (!$classId || !in_array($classId, $targetClassIds, true)) {
                    continue;
                }
                if (!in_array($classId, $enrolledClassIdsByStudent[$sid] ?? [], true)) {
                    continue;
                }

                if ($log->category === 'assignment' && !empty($meta['announcement_id'])) {
                    $loggedSubmissionKeys[$sid . '_' . (int) $meta['announcement_id']] = true;
                }

                $classroom = $classMap->get($classId);

                $entries->push([
                    'id'             => 'log_' . $log->id,
                    'student_id'     => $student->id,
                    'student_name'   => $this->displayName($student),
                    'student_email'  => $student->email,
                    'avatar'         => $student->avatar_url ?? $student->avatar ?? null,
                    'classroom_id'   => $classId,
                    'classroom_name' => $classroom?->name ?? ($meta['class_name'] ?? 'Classroom'),
                    'category'       => strtolower($log->category),
                    'action'         => $log->action,
                    'description'    => $log->description,
                    'detail_badge'   => $meta['file_name'] ?? ($classroom?->name ?? null),
                    'review_url'     => null,
                    'timestamp'      => $log->created_at ? Carbon::parse($log->created_at) : null,
                ]);
            }
        }

        // 2. Class Quiz Attempts (strictly quizzes belonging to the teacher's target classes)
        if (Schema::hasTable('quiz_attempts') && Schema::hasTable('quizzes') && class_exists(QuizAttempt::class)) {
            $attemptUserCol = Schema::hasColumn('quiz_attempts', 'user_id') ? 'user_id' : 'student_id';

            $attempts = QuizAttempt::with('quiz')
                ->whereIn($attemptUserCol, $studentIds)
                ->whereHas('quiz', function ($q) use ($targetClassIds) {
                    $q->whereIn('class_id', $targetClassIds);
                })
                ->latest()
                ->limit(500)
                ->get();

            foreach ($attempts as $attempt) {
                $sid     = (int) $attempt->{$attemptUserCol};
                $student = $studentMap->get($sid);
                $quiz    = $attempt->quiz;
                $classId = (int) ($quiz?->class_id ?? 0);

                if (!$student || !$quiz || !in_array($classId, $targetClassIds, true)) {
                    continue;
                }
                if (!in_array($classId, $enrolledClassIdsByStudent[$sid] ?? [], true)) {
                    continue;
                }

                $classroom = $classMap->get($classId);
                $className = $classroom?->name ?? 'Classroom';
                $score     = $attempt->score ?? 0;
                $total     = $attempt->total_questions ?? $attempt->max_score ?? null;
                $scoreText = $total ? "{$score}/{$total}" : "{$score} pts";
                $xpEarned  = $attempt->xp_earned ?? null;

                $entries->push([
                    'id'             => 'attempt_' . $attempt->id,
                    'student_id'     => $student->id,
                    'student_name'   => $this->displayName($student),
                    'student_email'  => $student->email,
                    'avatar'         => $student->avatar_url ?? $student->avatar ?? null,
                    'classroom_id'   => $classId,
                    'classroom_name' => $className,
                    'category'       => 'quiz',
                    'action'         => 'Completed Quiz',
                    'description'    => "Completed \"{$quiz->title}\" in {$className} with a score of {$scoreText}",
                    'detail_badge'   => $xpEarned ? "+{$xpEarned} XP • {$scoreText}" : $scoreText,
                    'review_url'     => Route::has('quiz.review') ? route('quiz.review', $attempt->id) : null,
                    'timestamp'      => $attempt->completed_at
                        ? Carbon::parse($attempt->completed_at)
                        : ($attempt->created_at ? Carbon::parse($attempt->created_at) : null),
                ]);
            }
        }

        // 3. Class Assignment Submissions (strictly assignments posted in the teacher's target classes)
        if (Schema::hasTable('assignment_submissions') && Schema::hasTable('announcements')) {
            $subStudentCol = Schema::hasColumn('assignment_submissions', 'student_id') ? 'student_id' : 'user_id';

            $submissions = DB::table('assignment_submissions')
                ->join('announcements', 'assignment_submissions.announcement_id', '=', 'announcements.id')
                ->whereIn("assignment_submissions.{$subStudentCol}", $studentIds)
                ->whereIn('announcements.class_id', $targetClassIds)
                ->orderByDesc('assignment_submissions.created_at')
                ->limit(500)
                ->get([
                    'assignment_submissions.id',
                    "assignment_submissions.{$subStudentCol} as student_ref_id",
                    'assignment_submissions.announcement_id',
                    'assignment_submissions.score',
                    'assignment_submissions.file_name',
                    'assignment_submissions.submitted_at',
                    'assignment_submissions.created_at',
                    'announcements.class_id',
                    'announcements.title as assignment_title',
                    'announcements.points as max_points',
                ]);

            foreach ($submissions as $sub) {
                $sid     = (int) $sub->student_ref_id;
                $classId = (int) $sub->class_id;
                $student = $studentMap->get($sid);

                if (!$student || !in_array($classId, $enrolledClassIdsByStudent[$sid] ?? [], true)) {
                    continue;
                }

                // Avoid duplicate rows if middleware already logged the exact same submission
                $dedupKey = $sid . '_' . (int) $sub->announcement_id;
                if (isset($loggedSubmissionKeys[$dedupKey])) {
                    continue;
                }

                $classroom = $classMap->get($classId);
                $className = $classroom?->name ?? 'Classroom';
                $title     = $sub->assignment_title ?: 'Class Assignment';
                $badge     = $sub->score !== null
                    ? "Graded: {$sub->score}/" . ($sub->max_points ?? 100)
                    : ($sub->file_name ? "📎 {$sub->file_name}" : 'Turned In');

                $entries->push([
                    'id'             => 'submission_' . $sub->id,
                    'student_id'     => $student->id,
                    'student_name'   => $this->displayName($student),
                    'student_email'  => $student->email,
                    'avatar'         => $student->avatar_url ?? $student->avatar ?? null,
                    'classroom_id'   => $classId,
                    'classroom_name' => $className,
                    'category'       => 'assignment',
                    'action'         => 'Submitted Assignment',
                    'description'    => "Submitted assignment \"{$title}\" in {$className}",
                    'detail_badge'   => $badge,
                    'review_url'     => Route::has('subjects.show') ? route('subjects.show', $classId) : null,
                    'timestamp'      => $sub->submitted_at
                        ? Carbon::parse($sub->submitted_at)
                        : ($sub->created_at ? Carbon::parse($sub->created_at) : null),
                ]);
            }
        }

        // Apply UI filters
        if ($request->filled('student_id')) {
            $targetId = (int) $request->input('student_id');
            $entries  = $entries->filter(fn ($row) => (int) $row['student_id'] === $targetId);
        }

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $cat     = strtolower((string) $request->input('category'));
            $entries = $entries->filter(fn ($row) => strtolower($row['category']) === $cat);
        }

        if ($request->filled('date')) {
            $date    = (string) $request->input('date');
            $entries = $entries->filter(fn ($row) => $row['timestamp'] && $row['timestamp']->toDateString() === $date);
        }

        if ($request->filled('search')) {
            $q       = mb_strtolower(trim((string) $request->input('search')));
            $entries = $entries->filter(function ($row) use ($q) {
                return str_contains(mb_strtolower($row['student_name']), $q)
                    || str_contains(mb_strtolower($row['student_email']), $q)
                    || str_contains(mb_strtolower($row['classroom_name'] ?? ''), $q)
                    || str_contains(mb_strtolower($row['action']), $q)
                    || str_contains(mb_strtolower($row['description']), $q);
            });
        }

        return $entries
            ->sortByDesc(fn ($row) => $row['timestamp'] ? $row['timestamp']->timestamp : 0)
            ->values();
    }

    protected function displayName(User $user): string
    {
        $full = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        return $full !== '' ? $full : ($user->name ?? 'Student #' . $user->id);
    }
}