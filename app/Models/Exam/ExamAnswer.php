<?php

namespace App\Models\Exam;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sin LogsActivity a propósito: 68k filas en activity_log copiando la respuesta
 * que esta misma tabla ya guarda de forma inmutable. La integridad del examen se
 * apoya en exam_answers, no en el audit trail. Ver [CourseProgress].
 */
class ExamAnswer extends Model
{
    use HasFactory;

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
