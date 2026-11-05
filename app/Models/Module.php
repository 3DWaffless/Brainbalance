<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id', // <-- Added to allow saving
        'topic_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }
}