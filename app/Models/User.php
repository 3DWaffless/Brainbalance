<?php
// FILE: app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'password',
        'role',
        'total_xp',
        'level',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ---------------------------------------------------------------
    // RELATIONSHIPS
    // ---------------------------------------------------------------

    // Classes this teacher owns
    public function ownedClasses()
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    // Classes this student has joined
    public function joinedClasses()
    {
        return $this->belongsToMany(Classroom::class, 'class_student', 'student_id', 'class_id')
                    ->withPivot('joined_at')
                    ->withTimestamps();
    }

    // ---------------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------------

    // Quiz attempts made by this student
    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'student_id');
    }

    // Minigame attempts made by this student
    public function minigameAttempts()
    {
        return $this->hasMany(MinigameAttempt::class, 'student_id');
    }

    // Per-topic progress records for this student
    public function progress()
    {
        return $this->hasMany(StudentProgress::class, 'student_id');
    }

    // Badges earned by this student
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'student_badges', 'student_id', 'badge_id')
                    ->withPivot('earned_at');
    }

    public function isStudent(): bool { return $this->role === 'student'; }
    public function isTeacher(): bool { return $this->role === 'teacher'; }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }
}