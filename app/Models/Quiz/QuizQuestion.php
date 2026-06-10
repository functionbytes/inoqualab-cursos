<?php

namespace App\Models\Quiz;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    use HasFactory,
        HasFinders;

    protected $table = 'quiz_questions';

    protected $fillable = [
        'slack',
        'lesson_id',
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

    public function lessons(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\CourseLesson', 'lesson_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo('App\Models\Quiz\QuizTopic', 'topic_id', 'id');
    }
}
