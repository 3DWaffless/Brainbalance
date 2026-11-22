<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'teacher_id',
        'name',
        'subject',
        'section',
        'class_code',
        'code',
    ];

    /**
     * Get the announcements posted in this classroom.
     */
    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'class_id')->latest();
    }

    /**
     * Get the teacher that owns the classroom.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * The students enrolled in the classroom.
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'class_student', 'class_id', 'student_id')
                    ->withPivot('joined_at')
                    ->withTimestamps();
    }

    /**
     * The quizzes assigned to this classroom.
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'class_id');
    }

    /**
     * The learning modules uploaded to this classroom.
     */
    public function modules()
    {
        return $this->hasMany(Module::class, 'class_id');
    }
}