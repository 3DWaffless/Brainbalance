<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'module_section_id',
        'title',
        'description',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'module_section_id',
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

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ModuleSection::class, 'module_section_id');
    }

    protected static function booted(): void
    {
        static::creating(function (Module $module) {
            if (empty($module->module_section_id) && request()->filled('module_section_id')) {
                $sectionId = (int) request()->input('module_section_id');
                $belongsToClass = \App\Models\ModuleSection::where('id', $sectionId)
                    ->where('class_id', $module->class_id)
                    ->exists();

                if ($belongsToClass) {
                    $module->module_section_id = $sectionId;
                }
            }
        });
    }

    public function moduleSection()
    {
        return $this->belongsTo(\App\Models\ModuleSection::class, 'module_section_id');
    }
}