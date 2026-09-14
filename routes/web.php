<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ModuleController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/auth/google', function () {
    return 'Google OAuth coming soon.';
})->name('auth.google');

Route::get('/terms', fn() => view('terms'))->name('terms');
Route::get('/privacy', fn() => view('privacy'))->name('privacy');

Route::middleware('auth')->group(function () {
    // Classroom Routes
    Route::get('/dashboard', [ClassroomController::class, 'dashboard'])->name('dashboard');
    Route::post('/classes', [ClassroomController::class, 'store'])->name('classes.store');
    Route::delete('/classes/{classroom}', [ClassroomController::class, 'destroy'])->name('classes.destroy');
    Route::delete('/classes/{classroom}/students/{student}', [ClassroomController::class, 'removeStudent'])->name('classes.removeStudent');
    Route::post('/classes/join', [ClassroomController::class, 'join'])->name('classes.join');
    Route::delete('/classes/{classroom}/leave', [ClassroomController::class, 'leave'])->name('classes.leave');

    // Quiz Routes
    Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
    Route::post('/quiz/start', [QuizController::class, 'start'])->name('quiz.start');
    Route::post('/quiz/answer', [QuizController::class, 'submitAnswer'])->name('quiz.answer');
    Route::get('/quiz/results', [QuizController::class, 'results'])->name('quiz.results');

    // Module Routes
    Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::post('/modules', [ModuleController::class, 'store'])->name('modules.store');
    Route::get('/modules/{module}/view', [ModuleController::class, 'view'])->name('modules.view');
    Route::get('/modules/{module}/download', [ModuleController::class, 'download'])->name('modules.download');
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');
    Route::post('/modules/{module}/ai-chat', [ModuleController::class, 'aiChat'])->name('modules.ai-chat');

    // Profile & Settings Routes
    Route::get('/settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/settings', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/settings', [ProfileController::class, 'destroy'])->name('profile.destroy');
});