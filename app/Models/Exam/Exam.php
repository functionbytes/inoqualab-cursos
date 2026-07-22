<?php

namespace App\Models\Exam;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory,
        HasFinders;

    protected $table = 'exams';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $fillable = [
        'course_id',
        'user_id',
        'inscription_id',
        'topic_id',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course', 'course_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo('App\Models\Exam\ExamTopic', 'topic_id', 'id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany('App\Models\Exam\ExamAnswer', 'exam_id');
    }
}
