<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinigameAttempt extends Model
{
    protected $fillable = [
        'student_id',
        'quiz_id',
        'score',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
