<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\StudentLogController;
use App\Models\StudentBadge;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Google OAuth SSO Endpoints
Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/google/complete', [SocialiteController::class, 'showCompleteForm'])->name('auth.google.complete');
Route::post('/auth/google/complete', [SocialiteController::class, 'completeRegistration'])->name('auth.google.complete.store');

Route::get('/terms', fn() => view('terms'))->name('terms');
Route::get('/privacy', fn() => view('privacy'))->name('privacy');

Route::middleware('auth')->group(function () {
    // Subject & Class Workspace Routes
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/class/{classroom}', [SubjectController::class, 'show'])->name('subjects.show');

    // Badge Notification Dismiss Route
    Route::post('/badges/mark-read', function () {
        StudentBadge::where('student_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    })->name('badges.markRead');

    // Unified Class Stream / Announcement Routes
    Route::post('/classes/{classroom}/announcements', [SubjectController::class, 'storeAnnouncement'])->name('announcements.store');
    Route::get('/announcements/{announcement}/view', [SubjectController::class, 'viewAttachment'])->name('announcements.view');
    Route::get('/announcements/{announcement}/download', [SubjectController::class, 'downloadAttachment'])->name('announcements.download');
    Route::delete('/announcements/{announcement}', [SubjectController::class, 'destroyAnnouncement'])->name('announcements.destroy');

    // Student & Teacher Assignments Routes
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments/{announcement}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');
    Route::post('/assignments/submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('assignments.grade');
    Route::get('/assignments/submissions/{submission}/download', [AssignmentController::class, 'downloadSubmission'])->name('assignments.submission.download');

    // Classroom Routes
    Route::get('/dashboard', [ClassroomController::class, 'dashboard'])->name('dashboard');
    Route::post('/classes', [ClassroomController::class, 'store'])->name('classes.store');
    Route::post('/classes/join', [ClassroomController::class, 'join'])->name('classes.join');
    Route::get('/classes/{classroom}/edit', [ClassroomController::class, 'edit'])->name('classes.edit');
    Route::put('/classes/{classroom}', [ClassroomController::class, 'update'])->name('classes.update');
    Route::post('/classes/{classroom}/regenerate-code', [ClassroomController::class, 'regenerateCode'])->name('classes.regenerate-code');
    Route::get('/classes/{classroom}', [ClassroomController::class, 'show'])->name('classes.show');
    Route::delete('/classes/{classroom}', [ClassroomController::class, 'destroy'])->name('classes.destroy');
    Route::delete('/classes/{classroom}/students/{student}', [ClassroomController::class, 'removeStudent'])->name('classes.removeStudent');
    Route::delete('/classes/{classroom}/leave', [ClassroomController::class, 'leave'])->name('classes.leave');

    // Teacher Student Activity Logs Routes
    Route::get('/teacher/student-logs', [StudentLogController::class, 'index'])->name('teacher.student-logs.index');
    Route::get('/teacher/student-logs/export', [StudentLogController::class, 'export'])->name('teacher.student-logs.export');

    // Quiz Routes
    Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
    Route::get('/quiz/results', [QuizController::class, 'results'])->name('quiz.results');
    Route::post('/quiz/exit', [QuizController::class, 'saveAndExit'])->name('quiz.exit');
    
    // Student Quiz History & Attempt Review
    Route::get('/quizzes/history', [QuizController::class, 'history'])->name('quiz.history');
    Route::get('/quizzes/attempt/{attempt}/review', [QuizController::class, 'review'])->name('quiz.review');

    // Quiz Creation Wizard (Teacher Only)
    Route::get('/quiz/create', [QuizController::class, 'create'])->name('quiz.create');
    Route::post('/quiz/generate', [QuizController::class, 'generate'])->name('quiz.generate');
    Route::post('/quiz/store', [QuizController::class, 'store'])->name('quiz.store');
    
    // Student Interactive Quiz Runner Routes (AJAX)
    Route::post('/quiz/start', [QuizController::class, 'start'])->name('quiz.start');
    Route::post('/quiz/answer', [QuizController::class, 'submitAnswer'])->name('quiz.submitAnswer');
    Route::post('/quiz/generate-pdf', [QuizController::class, 'generateFromPdf'])->name('quiz.generateFromPdf');

    // Edit, Publish, Unpublish, and Delete Routes
    Route::get('/quiz/{quiz}/edit', [QuizController::class, 'edit'])->name('quiz.edit');
    Route::put('/quiz/{quiz}', [QuizController::class, 'update'])->name('quiz.update');
    Route::post('/quiz/{quiz}/publish', [QuizController::class, 'publish'])->name('quiz.publish');
    Route::post('/quiz/{quiz}/unpublish', [QuizController::class, 'unpublish'])->name('quiz.unpublish');
    Route::delete('/quiz/{quiz}', [QuizController::class, 'destroy'])->name('quiz.destroy');

    // Teacher Groq AI Diagnostic Struggle Analysis Route
    Route::post('/quiz/{quiz}/analyze-struggles', [QuizController::class, 'analyzeStruggles'])->name('quiz.analyzeStruggles');

    // DYNAMIC ROUTE MUST GO LAST IN THE QUIZ SECTION
    Route::get('/quiz/{quiz}', [QuizController::class, 'show'])->name('quiz.show');

    // Module Routes
    Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::post('/modules', [ModuleController::class, 'store'])->name('modules.store');
    Route::get('/modules/{module}/read', [ModuleController::class, 'read'])->name('modules.read');
    Route::get('/modules/{module}/view', [ModuleController::class, 'view'])->name('modules.view');
    Route::get('/modules/{module}/download', [ModuleController::class, 'download'])->name('modules.download');
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');
    Route::post('/modules/{module}/ai-chat', [ModuleController::class, 'aiChat'])->name('modules.ai-chat');

    // Profile & Settings Routes
    Route::get('/settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['put', 'patch'], '/settings', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/settings', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Students CRUD (Admin Dashboard)
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/students', [App\Http\Controllers\AdminController::class, 'storeStudent'])->name('students.store');
    Route::put('/students/{user}', [App\Http\Controllers\AdminController::class, 'updateStudent'])->name('students.update');
    Route::delete('/users/{user}', [App\Http\Controllers\AdminController::class, 'destroyUser'])->name('users.destroy');

    // Subjects / Classrooms CRUD
    Route::get('/subjects', [App\Http\Controllers\AdminController::class, 'subjects'])->name('subjects.index');
    Route::post('/subjects', [App\Http\Controllers\AdminController::class, 'storeSubject'])->name('subjects.store');
    Route::put('/subjects/{classroom}', [App\Http\Controllers\AdminController::class, 'updateSubject'])->name('subjects.update');
    Route::delete('/classrooms/{classroom}', [App\Http\Controllers\AdminController::class, 'destroyClassroom'])->name('classrooms.destroy');

    // Quizzes CRUD
    Route::get('/quizzes', [App\Http\Controllers\AdminController::class, 'quizzes'])->name('quizzes.index');
    Route::post('/quizzes', [App\Http\Controllers\AdminController::class, 'storeQuiz'])->name('quizzes.store');
    Route::put('/quizzes/{quiz}', [App\Http\Controllers\AdminController::class, 'updateQuiz'])->name('quizzes.update');
    Route::delete('/quizzes/{quiz}', [App\Http\Controllers\AdminController::class, 'destroyQuiz'])->name('quizzes.destroy');

    // Modules Delete
    Route::delete('/modules/{module}', [App\Http\Controllers\AdminController::class, 'destroyModule'])->name('modules.destroy');
});