<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'exam_answers';

    protected $fillable = [
        'exam_id',
        'course_id',
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

    public function scopeCourses($query, $id)
    {
        return $query->where('course_id', $id);
    }

    public function scopeUsers($query, $id)
    {
        return $query->where('user_id', $id);
    }

    public function scopeTopics($query, $id)
    {
        return $query->where('topic_id', $id);
    }

    public function scopeWrong($query)
    {
        return $query->where('approved', 0);
    }

    public function scopeCorrect($query)
    {
        return $query->where('approved', 1);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo('App\Model\Exam', 'exam_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo('Ap\Models\QuizTopic', 'topic_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Model\User', 'question_id', 'id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo('App\Model\ExamQuestion', 'question_id', 'id');
    }
}
