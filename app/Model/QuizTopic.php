<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizTopic extends Model
{
    protected $table = 'quiz_topics';

    protected $connection = 'mysql_second';

    protected $fillable = [
        'slack',
        'title',
        'description',
        'per_q_mark',
        'timer',
        'available',
        'show_ans',
        'quiz_again',
        'course_id',
        'class_id',
        'due_days',
        'type',
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

    public function class(): BelongsTo
    {
        return $this->belongsTo('App\Model\CourseClass', 'class_id', 'id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany('App\Model\QuizAnswer', 'topic_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany('App\Model\QuizQuestion', 'topic_id');
    }
}
