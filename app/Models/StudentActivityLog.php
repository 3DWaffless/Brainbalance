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

    public static function record(User|int $user, string $action, string $category, string $description, array $metadata = []): ?self
    {
        try {
            if (!Schema::hasTable('student_activity_logs')) {
                return null;
            }

            $userId  = $user instanceof User ? $user->id : $user;
            $request = request();

            return static::create([
                'user_id'     => $userId,
                'action'      => $action,
                'category'    => $category, 
                'description' => $description,
                'metadata'    => empty($metadata) ? null : $metadata,
                'ip_address'  => $request?->ip(),
                'user_agent'  => $request ? mb_substr((string) $request->userAgent(), 0, 500) : null,
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }
}