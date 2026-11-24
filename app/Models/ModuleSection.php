<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class ModuleSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'teacher_id',
        'title',
        'notes',
        'position',
    ];

    protected static function booted(): void
    {
        // Automatically attach module_section_id to Announcements & Modules created from the class composer
        if (class_exists(Announcement::class)) {
            Announcement::creating(function ($announcement) {
                if (Schema::hasColumn('announcements', 'module_section_id') && empty($announcement->module_section_id) && request()->filled('module_section_id')) {
                    $sectionId = (int) request()->input('module_section_id');
                    $belongsToClass = static::where('id', $sectionId)
                        ->where('class_id', $announcement->class_id)
                        ->exists();

                    if ($belongsToClass) {
                        $announcement->module_section_id = $sectionId;
                    }
                }
            });

            Announcement::created(function ($announcement) {
                if (!empty($announcement->module_id) && !empty($announcement->module_section_id) && Schema::hasColumn('modules', 'module_section_id')) {
                    Module::where('id', $announcement->module_id)->update([
                        'module_section_id' => $announcement->module_section_id,
                    ]);
                }
            });
        }

        Module::creating(function (Module $module) {
            if (Schema::hasColumn('modules', 'module_section_id') && empty($module->module_section_id) && request()->filled('module_section_id')) {
                $sectionId = (int) request()->input('module_section_id');
                $belongsToClass = static::where('id', $sectionId)
                    ->where('class_id', $module->class_id)
                    ->exists();

                if ($belongsToClass) {
                    $module->module_section_id = $sectionId;
                }
            }
        });
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'module_section_id')->latest();
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class, 'module_section_id')->latest();
    }
}