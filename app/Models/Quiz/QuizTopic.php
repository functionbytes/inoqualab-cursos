<?php

namespace App\Models\Quiz;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizTopic extends Model
{
    use HasFactory,
        HasFinders;

    protected $table = 'quiz_topics';

    protected $fillable = [
        'slack',
        'title',
        'description',
        'timer',
        'per_q_mark',
        'available',
        'show_ans',
        'quiz_again',
        'lesson_id',
        'course_id',
        'due_days',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'timer' => 'integer',
            'per_q_mark' => 'integer',
            'available' => 'boolean',
            'show_ans' => 'integer',
            'quiz_again' => 'boolean',
            'due_days' => 'integer',
        ];
    }

    public function scopeId($query, $id)
    {
        $model = $query->where('id', $id)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function scopeSlack($query, $slack)
    {
        $model = $query->where('slack', $slack)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\CourseLesson');
    }

    public function answers(): HasMany
    {
        return $this->hasMany('App\Models\Quiz\QuizAnswer', 'topic_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany('App\Models\Quiz\QuizQuestion', 'topic_id');
    }
}
