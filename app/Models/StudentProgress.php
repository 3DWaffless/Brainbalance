<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProgress extends Model
{
    protected $table = 'student_progress';

    protected $fillable = [
        'student_id',
        'topic_id',
        'mastery_level',
        'status',
        'attempts_count',
        'last_ai_explanation',
        'ai_triggered_at',
        'last_attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'ai_triggered_at'    => 'datetime',
            'last_attempted_at'  => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}
