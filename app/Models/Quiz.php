<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'title',
        'description',
        'subject',
        'class_id',
        'topic_id',
        'type',
        'config',
        'published_at',
        'deadline_at',
        'max_attempts',
    ];

    protected function casts(): array
    {
        return [
            'config'       => 'array',
            'published_at' => 'datetime',
            'deadline_at'  => 'datetime',
        ];
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'quiz_questions');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function minigameAttempts()
    {
        return $this->hasMany(MinigameAttempt::class);
    }
}