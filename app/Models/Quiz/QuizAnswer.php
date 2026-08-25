<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $table = 'quiz_answers';

    protected $fillable = [
        'quiz_id',
        'lesson_id',
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
        return $this->belongsTo('App\Models\Quiz\Quiz', 'quiz_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo('App\Models\Quiz\QuizTopic', 'topic_id', 'id');
    }
}
