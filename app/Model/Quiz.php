<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'quizs';

    protected $fillable = [
        'user_id',
        'class_id',
        'topic_id',
        'course_id',
        'order_id',
        'wrong',
        'correct',
        'score',
        'created_at',
        'updated_at',
    ];

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo('App\Model\CourseClass', 'class_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo('App\Model\QuizTopic', 'topic_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo('App\Model\Order', 'order_id', 'id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany('App\Model\QuizAnswer', 'quiz_id');
    }
}
