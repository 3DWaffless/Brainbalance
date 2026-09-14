<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
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
}