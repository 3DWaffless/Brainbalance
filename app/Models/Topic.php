<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $fillable = [
        'parent_topic_id',
        'subject',
        'name',
        'deped_reference',
        'order_index',
        'difficulty_level',
        'lesson_content',
        'lesson_resource_url',
    ];

    public function parentTopic()
    {
        return $this->belongsTo(Topic::class, 'parent_topic_id');
    }

    public function childTopics()
    {
        return $this->hasMany(Topic::class, 'parent_topic_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }
}
