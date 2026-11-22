<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Classroom;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    protected function ensureAdmin(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized. Admin access only.');
        }
    }

    // ==========================================
    // 1. STUDENTS CRUD (Admin Dashboard)
    // ==========================================
    public function dashboard()
    {
        $this->ensureAdmin();

        $stats = [
            'students'   => User::where('role', 'student')->count(),
            'teachers'   => User::where('role', 'teacher')->count(),
            'classrooms' => Classroom::count(),
            'quizzes'    => Quiz::count(),
            'attempts'   => QuizAttempt::count(),
        ];

        $students = User::where('role', 'student')->latest()->get();
        $teachers = User::where('role', 'teacher')->latest()->get();

        return view('admin.dashboard', compact('stats', 'students', 'teachers'));
    }

    public function storeStudent(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email',
            'password'   => 'required|string|min:8',
        ]);

        $student = new User();
        if (Schema::hasColumn('users', 'name')) {
            $student->name = trim($request->first_name . ' ' . $request->last_name);
        }
        if (Schema::hasColumn('users', 'first_name')) {
            $student->first_name = $request->first_name;
        }
        if (Schema::hasColumn('users', 'last_name')) {
            $student->last_name = $request->last_name;
        }
        $student->email = $request->email;
        $student->password = Hash::make($request->password);
        $student->role = 'student';
        $student->save();

        return back()->with('success', 'Student account created successfully.');
    }

    public function updateStudent(Request $request, User $user)
    {
        $this->ensureAdmin();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'   => 'nullable|string|min:8',
        ]);

        if (Schema::hasColumn('users', 'name')) {
            $user->name = trim($request->first_name . ' ' . $request->last_name);
        }
        if (Schema::hasColumn('users', 'first_name')) {
            $user->first_name = $request->first_name;
        }
        if (Schema::hasColumn('users', 'last_name')) {
            $user->last_name = $request->last_name;
        }
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Student updated successfully.');
    }

    public function destroyUser(User $user)
    {
        $this->ensureAdmin();
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }

    // ==========================================
    // 2. SUBJECTS / CLASSROOMS CRUD
    // ==========================================
    public function subjects()
    {
        $this->ensureAdmin();

        $classrooms = Classroom::with(['teacher', 'students'])->latest()->get();
        $teachers = User::where('role', 'teacher')->get();

        return view('admin.subjects', compact('classrooms', 'teachers'));
    }

    public function storeSubject(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'name'        => 'required|string|max:255',
            'subject'     => 'nullable|string|max:255',
            'teacher_id'  => 'required|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $classroom = new Classroom();
        $table = $classroom->getTable();

        $classroom->name = $request->name;
        if (Schema::hasColumn($table, 'subject')) {
            $classroom->subject = $request->subject ?? $request->name;
        }
        if (Schema::hasColumn($table, 'description')) {
            $classroom->description = $request->description;
        }
        if (Schema::hasColumn($table, 'code')) {
            $classroom->code = strtoupper(Str::random(6));
        }
        $classroom->teacher_id = $request->teacher_id;
        $classroom->save();

        return back()->with('success', 'Subject / Classroom created successfully.');
    }

    public function updateSubject(Request $request, Classroom $classroom)
    {
        $this->ensureAdmin();

        $request->validate([
            'name'        => 'required|string|max:255',
            'subject'     => 'nullable|string|max:255',
            'teacher_id'  => 'required|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $table = $classroom->getTable();

        $classroom->name = $request->name;
        if (Schema::hasColumn($table, 'subject')) {
            $classroom->subject = $request->subject ?? $request->name;
        }
        if (Schema::hasColumn($table, 'description')) {
            $classroom->description = $request->description;
        }
        $classroom->teacher_id = $request->teacher_id;
        $classroom->save();

        return back()->with('success', 'Subject / Classroom updated successfully.');
    }

    public function destroyClassroom(Classroom $classroom)
    {
        $this->ensureAdmin();
        $classroom->delete();
        return back()->with('success', 'Subject / Classroom deleted successfully.');
    }

    // ==========================================
    // 3. QUIZZES CRUD
    // ==========================================
    public function quizzes()
    {
        $this->ensureAdmin();

        $quizzes = Quiz::with('classroom')->latest()->get();
        $classrooms = Classroom::all();

        return view('admin.quizzes', compact('quizzes', 'classrooms'));
    }

    public function storeQuiz(Request $request)
    {
        $this->ensureAdmin();

        $classroomTable = (new Classroom)->getTable();

        $request->validate([
            'title'        => 'required|string|max:255',
            'classroom_id' => "required|exists:{$classroomTable},id",
            'description'  => 'nullable|string',
            'time_limit'   => 'nullable|integer|min:1',
        ]);

        $quiz = new Quiz();
        $quiz->title = $request->title;

        if (Schema::hasColumn('quizzes', 'class_id')) {
            $quiz->class_id = $request->classroom_id;
        } elseif (Schema::hasColumn('quizzes', 'classroom_id')) {
            $quiz->classroom_id = $request->classroom_id;
        }

        if (Schema::hasColumn('quizzes', 'subject')) {
            $classroom = Classroom::find($request->classroom_id);
            $quiz->subject = $classroom->subject ?? $classroom->name ?? 'General';
        }
        if (Schema::hasColumn('quizzes', 'description')) {
            $quiz->description = $request->description;
        }
        if (Schema::hasColumn('quizzes', 'time_limit')) {
            $quiz->time_limit = $request->time_limit;
        }
        if (Schema::hasColumn('quizzes', 'teacher_id')) {
            $classroom = Classroom::find($request->classroom_id);
            $quiz->teacher_id = $classroom->teacher_id ?? auth()->id();
        }
        $quiz->save();

        return back()->with('success', 'Quiz created successfully.');
    }

    public function updateQuiz(Request $request, Quiz $quiz)
    {
        $this->ensureAdmin();

        $classroomTable = (new Classroom)->getTable();

        $request->validate([
            'title'        => 'required|string|max:255',
            'classroom_id' => "required|exists:{$classroomTable},id",
            'description'  => 'nullable|string',
            'time_limit'   => 'nullable|integer|min:1',
        ]);

        $quiz->title = $request->title;

        if (Schema::hasColumn('quizzes', 'class_id')) {
            $quiz->class_id = $request->classroom_id;
        } elseif (Schema::hasColumn('quizzes', 'classroom_id')) {
            $quiz->classroom_id = $request->classroom_id;
        }

        if (Schema::hasColumn('quizzes', 'description')) {
            $quiz->description = $request->description;
        }
        if (Schema::hasColumn('quizzes', 'time_limit')) {
            $quiz->time_limit = $request->time_limit;
        }
        $quiz->save();

        return back()->with('success', 'Quiz updated successfully.');
    }

    public function destroyQuiz(Quiz $quiz)
    {
        $this->ensureAdmin();
        $quiz->delete();
        return back()->with('success', 'Quiz deleted successfully.');
    }

    public function destroyModule(Module $module)
    {
        $this->ensureAdmin();
        $module->delete();
        return back()->with('success', 'Module file deleted successfully.');
    }
}