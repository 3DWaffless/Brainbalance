<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'criteria_type',
        'criteria_value',
    ];

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_badges', 'badge_id', 'student_id')
                    ->withPivot('earned_at');
    }
}
