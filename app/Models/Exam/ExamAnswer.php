<?php

namespace App\Models\Exam;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ExamAnswer extends Model
{
    use HasFactory, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");
    }

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
        return $this->belongsTo('App\Models\Exam\Exam', 'exam_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course', 'course_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo('App\Models\Exam\ExamTopic', 'topic_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo('App\Models\Exam\ExamQuestion', 'question_id', 'id');
    }
}
