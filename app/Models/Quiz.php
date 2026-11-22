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
        'is_published',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'config'       => 'array',
            'published_at' => 'datetime',
            'deadline_at'  => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function isPublished(): bool
    {
        if (array_key_exists('is_published', $this->attributes) && $this->attributes['is_published'] !== null) {
            return (bool) $this->attributes['is_published'];
        }

        if (array_key_exists('status', $this->attributes) && $this->attributes['status'] !== null) {
            return $this->attributes['status'] === 'published';
        }

        if (is_array($this->config) && array_key_exists('is_draft', $this->config)) {
            return !$this->config['is_draft'] && $this->published_at !== null;
        }

        return $this->published_at !== null;
    }

    public function isDraft(): bool
    {
        return !$this->isPublished();
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