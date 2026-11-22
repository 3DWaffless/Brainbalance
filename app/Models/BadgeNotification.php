<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgeNotification extends Model
{
    protected $fillable = [
        'student_id',
        'badge_id',
        'quiz_id',
        'type',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function badge()
    {
        return $this->belongsTo(Badge::class, 'badge_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }
}