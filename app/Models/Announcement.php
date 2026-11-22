<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'class_id',
        'teacher_id',
        'type',
        'title',
        'content',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'video_url',
        'quiz_id',
        'module_id',
        'due_at',
        'points',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'points' => 'integer',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class, 'announcement_id');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) ($this->file_size ?? 0);
        if ($bytes <= 0) {
            return '';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        return max(1, round($bytes / 1024)) . ' KB';
    }

    public function getEmbedVideoUrlAttribute(): ?string
    {
        $url = trim((string) $this->video_url);
        if ($url === '') {
            return null;
        }

        // YouTube watch / short / shorts URLs
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        // Vimeo URLs
        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        return null;
    }

    /**
     * Human-friendly countdown text & urgency badge state for Assignments.
     */
    public function getDueStatusAttribute(): array
    {
        if (!$this->due_at) {
            return [
                'label' => 'No due date',
                'state' => 'none',
            ];
        }

        $now = Carbon::now();
        $due = $this->due_at;

        if ($due->isPast()) {
            $diff = $due->diffForHumans($now, ['parts' => 2, 'short' => false, 'syntax' => Carbon::DIFF_ABSOLUTE]);
            return [
                'label' => 'Overdue by ' . $diff,
                'state' => 'overdue',
            ];
        }

        $diff = $now->diffForHumans($due, ['parts' => 2, 'short' => false, 'syntax' => Carbon::DIFF_ABSOLUTE]);
        $hoursLeft = $now->diffInHours($due, false);

        return [
            'label' => 'Due in ' . $diff,
            'state' => $hoursLeft <= 24 ? 'urgent' : 'upcoming',
        ];
    }
}