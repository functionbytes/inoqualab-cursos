<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamTopic extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'exam_topics';

    protected $fillable = [
        'slack',
        'title',
        'description',
        'per_q_mark',
        'timer',
        'available',
        'show_ans',
        'quiz_again',
        'due_days',
        'type',
        'course_id',
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

    public function questions(): HasMany
    {
        return $this->hasMany('App\Model\ExamQuestion', 'topic_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }
}
