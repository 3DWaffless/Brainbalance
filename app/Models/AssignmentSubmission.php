<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'announcement_id',
        'student_id',
        'answer_text',
        'file_path',
        'file_name',
        'score',
        'teacher_feedback',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'score'        => 'integer',
    ];

    public function assignment()
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}