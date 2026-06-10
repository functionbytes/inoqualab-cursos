<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseClass extends Model
{
    protected $table = 'course_classes';

    protected $connection = 'mysql_second';

    protected $fillable = [
        'slack',
        'title',
        'detail',
        'image',
        'zip',
        'pdf',
        'audio',
        'file',
        'video',
        'url',
        'duration',
        'size',
        'preview_video',
        'available',
        'position',
        'type_id',
        'course_id',
        'chapter_id',
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

    public function scopeQuizs($query)
    {
        return $query->where('type_id', 6);
    }

    public function scopeChapters($query, $id)
    {
        return $query->where('chapter_id', $id);
    }

    public function scopeCourses($query, $id)
    {
        return $query->where('course_id', $id);
    }

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo('App\Model\CourseChapter', 'chapter_id', 'id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo('App\Model\Type', 'type_id', 'id');
    }

    public function quiztopic(): HasOne
    {
        return $this->hasOne('App\Model\QuizTopic', 'class_id');
    }

    public function subtitle(): HasMany
    {
        return $this->hasMany('App\Model\Subtitle', 'c_id');
    }
}
