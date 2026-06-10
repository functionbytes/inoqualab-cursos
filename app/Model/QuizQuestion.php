<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    protected $table = 'quiz_questions';

    protected $connection = 'mysql_second';

    protected $fillable = [
        'slack',
        'class_id',
        'topic_id',
        'question',
        'a',
        'b',
        'c',
        'd',
        'type',
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

    public function class(): BelongsTo
    {
        return $this->belongsTo('App\Model\CourseClass', 'class_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo('App\Model\QuizTopic', 'topic_id', 'id');
    }
}
