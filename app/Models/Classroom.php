<?php
// FILE: app/Models/Classroom.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Classroom extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'teacher_id',
        'name',
        'description',
        'code',
    ];

    // ---------------------------------------------------------------
    // RELATIONSHIPS
    // ---------------------------------------------------------------

    // The teacher who owns this class
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // All students in this class
    public function students()
    {
        return $this->belongsToMany(User::class, 'class_student', 'class_id', 'student_id')
                    ->withPivot('joined_at')
                    ->withTimestamps();
    }

    // ---------------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------------

    // Generate a unique class code like "BB-A1X9"
    public static function generateCode(): string
    {
        do {
            $code = 'BB-' . strtoupper(Str::random(4));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}