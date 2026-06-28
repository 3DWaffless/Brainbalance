<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;

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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/terms', fn() => view('terms'))->name('terms');
Route::get('/privacy', fn() => view('privacy'))->name('privacy');

use App\Http\Controllers\ClassroomController;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [ClassroomController::class, 'dashboard'])->name('dashboard');
    Route::post('/classes', [ClassroomController::class, 'store'])->name('classes.store');
    Route::delete('/classes/{classroom}', [ClassroomController::class, 'destroy'])->name('classes.destroy');
    Route::delete('/classes/{classroom}/students/{student}', [ClassroomController::class, 'removeStudent'])->name('classes.removeStudent');
    Route::post('/classes/join', [ClassroomController::class, 'join'])->name('classes.join');
    Route::delete('/classes/{classroom}/leave', [ClassroomController::class, 'leave'])->name('classes.leave');
});