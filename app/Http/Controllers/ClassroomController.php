<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function destroy(Classroom $classroom)
    {
        abort_unless(Auth::user()->isTeacher() && $classroom->teacher_id === Auth::id(), 403);

        $classroom->delete();

        return redirect()->route('dashboard')->with('success', 'Classroom deleted successfully!');
    }
}