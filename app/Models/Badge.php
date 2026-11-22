<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $table = 'badges';

    protected $fillable = [
        'name',
        'description',
        'icon',
        'criteria_type',
        'criteria_value',
    ];

    public function getImageUrlAttribute(): string
    {
        $baseName = pathinfo($this->icon ?? '1stplace', PATHINFO_FILENAME);

        foreach (['png', 'webp', 'jpg', 'jpeg', 'svg', 'PNG', 'JPG'] as $ext) {
            if (file_exists(public_path("images/{$baseName}.{$ext}"))) {
                return asset("images/{$baseName}.{$ext}");
            }
        }

        return asset("images/{$baseName}.png");
    }
}