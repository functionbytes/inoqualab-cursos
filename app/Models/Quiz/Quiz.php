<?php

namespace App\Models\Quiz;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sin LogsActivity a propósito: 433k filas en activity_log que duplicaban el
 * intento de quiz ya persistido aquí (con wrong/correct/score). Ninguna
 * interfaz consultaba ese audit trail. Ver [CourseProgress] por el mismo motivo.
 */
class Quiz extends Model
{
    use HasFactory,
        HasFinders;

    protected $table = 'quizs';

    protected $fillable = [
        'id',
        'user_id',
        'lesson_id',
        'topic_id',
        'course_id',
        'inscription_id',
        'order_id',
        'wrong',
        'correct',
        'score',
        'created_at',
        'updated_at',
    ];

    public function scopeId($query, $id)
    {
        $model = $query->where('id', $id)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\CourseLesson', 'lesson_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo('App\Models\Quiz\QuizTopic', 'topic_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course', 'course_id', 'id');
    }

    public function inscription(): BelongsTo
    {
        return $this->belongsTo('App\Models\Inscription', 'inscription_id', 'id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany('App\Models\Quiz\QuizAnswer', 'quiz_id');
    }
}
