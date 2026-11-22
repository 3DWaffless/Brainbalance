<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'title',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    /**
     * Dynamically convert raw bytes to KB or MB for UI rendering.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes <= 0) {
            return 'N/A';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }

        return number_format($bytes / 1024, 0) . ' KB';
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }
}