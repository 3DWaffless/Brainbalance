<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentBadge extends Model
{
    protected $table = 'student_badges';

    protected $fillable = [
        'student_id',
        'badge_id',
        'class_id',
        'quiz_id',
        'earned_at',
        'is_read',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'is_read'   => 'boolean',
    ];

    public function badge()
    {
        return $this->belongsTo(Badge::class, 'badge_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }
}