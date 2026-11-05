<?php // FILE: app/Http/Controllers/ClassroomController.php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Quiz; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    // ---------------------------------------------------------------
    // DASHBOARD   shows different view based on role
    // ---------------------------------------------------------------
public function dashboard()
    {
        $user = Auth::user();

        if ($user->isTeacher()) {
            // Teacher sees their own classes + student counts
            $classes = $user->ownedClasses()->withCount('students')->latest()->get();
            
            // Calculate total students across all classes
            $totalStudents = $classes->sum('students_count');

            // Fetch quizzes belonging to any of the teacher's classes
            $quizzes = Quiz::whereIn('class_id', $classes->pluck('id'))->latest()->get();

            return view('dashboard', compact('classes', 'quizzes', 'totalStudents'));
        }

        // Student sees classes they've joined
        $classes = $user->joinedClasses()->with('teacher')->latest()->get();
        
        // Fetch quizzes attached to the student's joined classes
        $quizzes = Quiz::whereIn('class_id', $classes->pluck('id'))->latest()->get();

        return view('dashboard', compact('classes', 'quizzes'));
    }

    /**
     * Display the specific class workspace (Students, Quizzes, Modules)
     */
public function show(Classroom $classroom)
    {
        $user = Auth::user();

        if ($user->isTeacher()) {
            abort_unless($classroom->teacher_id === $user->id, 403, 'You do not own this class.');
        } else {
            abort_unless($user->joinedClasses->contains($classroom->id), 403, 'You are not enrolled in this class.');
        }

        $classroom->load('students');
        $quizzes = \App\Models\Quiz::where('class_id', $classroom->id)->latest()->get();
        
        // Fetch the existing modules for this class
        $modules = \App\Models\Module::where('class_id', $classroom->id)->latest()->get(); 

        return view('classroom.show', compact('classroom', 'quizzes', 'modules'));
    }

    
    // ---------------------------------------------------------------
    // TEACHER — create a class
    // ---------------------------------------------------------------

    public function store(Request $request)
    {
        // Only teachers can create classes
        if (!Auth::user()->isTeacher()) {
            return back()->with('error', 'Only teachers can create classes.');
        }

        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Classroom::create([
            'teacher_id'  => Auth::id(),
            'name'        => $request->name,
            'description' => $request->description,
            'code'        => Classroom::generateCode(),
        ]);

        return back()->with('success', 'Class created successfully!');
    }

    // ---------------------------------------------------------------
    // TEACHER — delete a class
    // ---------------------------------------------------------------

    public function destroy(Classroom $classroom)
    {
        // Make sure only the owner can delete
        if ($classroom->teacher_id !== Auth::id()) {
            return back()->with('error', 'You do not own this class.');
        }

        $classroom->delete();

        return back()->with('success', 'Class deleted.');
    }

    // ---------------------------------------------------------------
    // TEACHER — remove a student from class
    // ---------------------------------------------------------------

    public function removeStudent(Classroom $classroom, $studentId)
    {
        if ($classroom->teacher_id !== Auth::id()) {
            return back()->with('error', 'You do not own this class.');
        }

        $classroom->students()->detach($studentId);

        return back()->with('success', 'Student removed from class.');
    }

    // ---------------------------------------------------------------
    // STUDENT — join a class using a code
    // ---------------------------------------------------------------

    public function join(Request $request)
    {
        // Only students can join classes
        if (!Auth::user()->isStudent()) {
            return back()->with('error', 'Only students can join classes.');
        }

        $request->validate([
            'code' => ['required', 'string', 'max:10'],
        ]);

        // Find the class by code
        $classroom = Classroom::where('code', strtoupper(trim($request->code)))->first();

        if (!$classroom) {
            return back()->withErrors(['code' => 'Invalid class code. Please check and try again.']);
        }

        // Check if already joined
        $alreadyJoined = Auth::user()->joinedClasses()->where('class_id', $classroom->id)->exists();

        if ($alreadyJoined) {
            return back()->withErrors(['code' => 'You are already in this class!']);
        }

        // Add student to class
        Auth::user()->joinedClasses()->attach($classroom->id, [
            'joined_at' => now(),
        ]);

        return back()->with('success', "You joined {$classroom->name}!");
    }
}