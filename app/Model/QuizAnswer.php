<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    protected $table = 'quiz_answers';

    protected $connection = 'mysql_second';

    protected $fillable = [
        'quiz_id',
        'class_id',
        'topic_id',
        'user_id',
        'question_id',
        'user_answer',
        'answer',
        'type',
        'approved',
        'created_at',
        'updated_at',
    ];

    public function scopeQuizs($query, $id)
    {
        return $query->where('quiz_id', $id);
    }

    public function scopeUsers($query, $id)
    {
        return $query->where('user_id', $id);
    }

    public function scopeTopics($query, $id)
    {
        return $query->where('topic_id', $id);
    }

    public function scopeCorrect($query)
    {
        return $query->where('approved', 1);
    }

    public function scopeWrong($query)
    {
        return $query->where('approved', 0);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo('App\Model\Quiz', 'question_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo('App\Model\QuizTopic', 'topic_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }
}
