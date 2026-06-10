<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    protected $table = 'exam_questions';

    protected $connection = 'mysql_second';

    protected $fillable = [
        'slack',
        'course_id',
        'topic_id',
        'question',
        'a',
        'b',
        'c',
        'd',
        'answer',
        'available',
        'created_at',
        'updated_at',
    ];

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function scopeSlack($query, $slack)
    {
        return $query->where('slack', $slack)->first();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo('App\Model\QuizTopic', 'topic_id', 'id');
    }
}
