<?php
// FILE: app/Models/StudentActivityLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class StudentActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'class_id',
        'action',
        'category',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public static function record(User|int $user, string $action, string $category, string $description, array $metadata = [], ?int $classId = null): ?self
    {
        try {
            if (!Schema::hasTable('student_activity_logs')) {
                return null;
            }

            $userId          = $user instanceof User ? $user->id : $user;
            $resolvedClassId = $classId ?? (isset($metadata['class_id']) ? (int) $metadata['class_id'] : null);
            $request         = request();

            if ($resolvedClassId && !isset($metadata['class_id'])) {
                $metadata['class_id'] = $resolvedClassId;
            }

            $payload = [
                'user_id'     => $userId,
                'action'      => $action,
                'category'    => $category,
                'description' => $description,
                'metadata'    => empty($metadata) ? null : $metadata,
                'ip_address'  => $request?->ip(),
                'user_agent'  => $request ? mb_substr((string) $request->userAgent(), 0, 500) : null,
            ];

            if (Schema::hasColumn('student_activity_logs', 'class_id')) {
                $payload['class_id'] = $resolvedClassId;
            }

            return static::create($payload);
        } catch (\Throwable $e) {
            return null;
        }
    }
}