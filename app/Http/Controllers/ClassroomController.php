<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ClassroomController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        if ($user->isTeacher()) {
            // Fetch teacher's owned classes with student counts
            $ownedClasses = Classroom::where('teacher_id', $user->id)
                ->withCount('students')
                ->with(['quizzes', 'students'])
                ->latest()
                ->get();

            $joinedClasses = collect();

            // Fetch quizzes created for the teacher's classes (eager-load classroom)
            $classIds = $ownedClasses->pluck('id');
            $recentQuizzes = Quiz::with('classroom')
                ->whereIn('class_id', $classIds)
                ->latest()
                ->take(5)
                ->get();

            // Calculate aggregate student count and published quizzes across all classes
            $totalStudentsCount = $ownedClasses->sum('students_count');
            $activeClassesCount = $ownedClasses->count();
            $publishedQuizzesCount = Quiz::whereIn('class_id', $classIds)
                ->whereNotNull('published_at')
                ->get()
                ->filter(fn ($q) => $q->isPublished())
                ->count();

            return view('dashboard', compact(
                'ownedClasses',
                'joinedClasses',
                'recentQuizzes',
                'totalStudentsCount',
                'activeClassesCount',
                'publishedQuizzesCount'
            ));
        } else {
            // Fetch student's joined classes
            $ownedClasses = collect();
            $joinedClasses = $user->joinedClasses()
                ->withCount('students')
                ->with('teacher')
                ->latest()
                ->get();

            $classIds = $joinedClasses->pluck('id');
            $recentQuizzes = Quiz::with('classroom')
                ->whereIn('class_id', $classIds)
                ->whereNotNull('published_at')
                ->latest()
                ->get()
                ->filter(fn ($q) => $q->isPublished())
                ->take(5)
                ->values();

            return view('dashboard', compact(
                'ownedClasses',
                'joinedClasses',
                'recentQuizzes'
            ));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
        ]);

        // Generate a unique non-repeating class code
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (Classroom::where('class_code', $code)->exists());

        Classroom::create([
            'teacher_id' => Auth::id(),
            'name' => $request->name,
            'subject' => $request->subject,
            'section' => $request->section,
            'class_code' => $code,
        ]);

        return redirect()->route('subjects.index')->with('status', 'Classroom created successfully! Class Code: ' . $code);
    }

    public function join(Request $request)
    {
        $request->validate([
            'class_code' => 'required|string',
        ]);

        $classroom = Classroom::where('class_code', strtoupper(trim($request->class_code)))->first();

        if (!$classroom) {
            return back()
                ->withErrors(['class_code' => 'Invalid class code. Please check and try again.'])
                ->with('error', 'Invalid class code. Please check and try again.');
        }

        Auth::user()->joinedClasses()->syncWithoutDetaching([
            $classroom->id => ['joined_at' => now()],
        ]);

        return redirect()->route('dashboard')->with('success', "Successfully joined {$classroom->name}!");
    }

    public function show(Classroom $classroom)
    {
        $user = Auth::user();

        if ($user->isTeacher() && $classroom->teacher_id !== $user->id) {
            abort(403);
        }

        if ($user->isStudent() && !$user->joinedClasses->contains($classroom->id)) {
            abort(403);
        }

        if ($user->isStudent()) {
            $classroom->load([
                'teacher',
                'students',
                'quizzes' => fn ($q) => $q->whereNotNull('published_at')->latest(),
                'modules',
            ]);
            $classroom->setRelation(
                'quizzes',
                $classroom->quizzes->filter(fn ($q) => $q->isPublished())->values()
            );
        } else {
            $classroom->load(['teacher', 'students', 'quizzes', 'modules']);
        }

        return view('classes.show', compact('classroom'));
    }

    protected function authorizeClassroomOwner(Classroom $classroom): void
    {
        $user = auth()->user();

        if (!$user || !in_array($user->role, ['teacher', 'admin'], true) || ($user->role !== 'admin' && (int) $classroom->teacher_id !== (int) $user->id)) {
            abort(403, 'You are not authorized to modify or delete this classroom.');
        }
    }

    protected function generateUniqueClassCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
            $exists = Classroom::query()
                ->when(Schema::hasColumn('classes', 'class_code'), fn ($q) => $q->orWhere('class_code', $code))
                ->when(Schema::hasColumn('classes', 'code'), fn ($q) => $q->orWhere('code', $code))
                ->exists();
        } while ($exists);

        return $code;
    }

    public function edit(Classroom $classroom)
    {
        $this->authorizeClassroomOwner($classroom);

        return view('classes.edit', compact('classroom'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $this->authorizeClassroomOwner($classroom);

        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'subject'     => 'nullable|string|max:100',
            'grade_level' => 'nullable|string|max:50',
            'section'     => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        $classroom->name = trim($validated['name']);

        if (Schema::hasColumn('classes', 'subject') && filled($validated['subject'] ?? null)) {
            $classroom->subject = trim($validated['subject']);
        }
        if (Schema::hasColumn('classes', 'grade_level')) {
            $classroom->grade_level = filled($validated['grade_level'] ?? null) ? trim($validated['grade_level']) : null;
        }
        if (Schema::hasColumn('classes', 'section')) {
            $classroom->section = filled($validated['section'] ?? null) ? trim($validated['section']) : null;
        }
        if (Schema::hasColumn('classes', 'description')) {
            $classroom->description = filled($validated['description'] ?? null) ? trim($validated['description']) : null;
        }

        $classroom->save();

        return redirect()
            ->route('subjects.show', $classroom->id)
            ->with('success', 'Classroom details updated successfully!');
    }

    public function regenerateCode(Classroom $classroom)
    {
        $this->authorizeClassroomOwner($classroom);

        $newCode = $this->generateUniqueClassCode();

        if (Schema::hasColumn('classes', 'class_code')) {
            $classroom->class_code = $newCode;
        }
        if (Schema::hasColumn('classes', 'code')) {
            $classroom->code = $newCode;
        }

        $classroom->save();

        return redirect()
            ->route('subjects.show', $classroom->id)
            ->with('success', "New classroom join code generated: {$newCode}");
    }

    public function destroy(Classroom $classroom)
    {
        $this->authorizeClassroomOwner($classroom);

        $classroomName = $classroom->name;

        if (method_exists($classroom, 'students')) {
            $classroom->students()->detach();
        }

        $classroom->delete();

        return redirect()
            ->route('subjects.index')
            ->with('success', "Classroom \"{$classroomName}\" has been deleted.");
    }
}