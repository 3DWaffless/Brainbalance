<?php
// FILE: app/Http/Controllers/ClassroomController.php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    // ---------------------------------------------------------------
    // DASHBOARD — shows different view based on role
    // ---------------------------------------------------------------

    public function dashboard()
    {
        $user = Auth::user();

        if ($user->isTeacher()) {
            // Teacher sees their own classes + student counts
            $classes = $user->ownedClasses()->withCount('students')->latest()->get();
            return view('dashboard', compact('classes'));
        }

        // Student sees classes they've joined
        $classes = $user->joinedClasses()->with('teacher')->latest()->get();
        return view('dashboard', compact('classes'));
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
        Auth::user()->joinedClasses()->attach($classroom->id);

        return back()->with('success', "You joined {$classroom->name}!");
    }
}