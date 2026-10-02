<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    /**
     * Display enrolled classes grouped by subject.
     */
    public function index()
    {
        $user = Auth::user();

        // Fetch classes for teacher or student with relationships loaded
        $classes = $user->isTeacher()
            ? Classroom::where('teacher_id', $user->id)->withCount('students')->get()
            : $user->joinedClasses()->with('teacher')->get();

        // Group classes by subject column (falls back to 'General' if empty)
        $subjects = $classes->groupBy(function ($class) {
            return $class->subject ?? 'General';
        });

        return view('subjects.index', compact('classes', 'subjects'));
    }

    /**
     * Display a specific class workspace along with its modules and quizzes.
     */
    public function show(Classroom $classroom)
    {
        $user = Auth::user();

        // Security check: ensure student is enrolled or teacher owns the class
        if ($user->isStudent() && !$user->joinedClasses->contains($classroom->id)) {
            abort(403, 'You are not enrolled in this class.');
        }

        if ($user->isTeacher() && $classroom->teacher_id !== $user->id) {
            abort(403, 'Unauthorized access to this classroom.');
        }

        $modules = $classroom->modules()->latest()->get();
        $quizzes = $classroom->quizzes()->latest()->get();

        return view('subjects.show', compact('classroom', 'modules', 'quizzes'));
    }
}