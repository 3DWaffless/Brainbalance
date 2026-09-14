<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'quiz_id',
        'weakest_topic_id',
        'score',
        'total_questions',
        'time_taken_seconds',
        'current_streak',
        'max_streak',
        'total_xp',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function weakestTopic()
    {
        return $this->belongsTo(Topic::class, 'weakest_topic_id');
    }

    public function responses()
    {
        return $this->hasMany(QuestionResponse::class);
    }

    public function getAccuracyAttribute()
    {
        if ($this->total_questions == 0) return 0;
        return round(($this->score / $this->total_questions) * 100);
    }
}