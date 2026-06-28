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
                    ->withTimestamps();
    }

    // ---------------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------------

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