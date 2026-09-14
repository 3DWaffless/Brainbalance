<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'topic_id',
        'type',
        'title',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
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
