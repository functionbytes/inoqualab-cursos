<?php

namespace App\Models\Course;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CourseLesson extends Model implements HasMedia
{
    use HasFactory,
        HasFinders, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $table = 'course_lessons';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $fillable = [
        'slack',
        'title',
        'detail',
        'content',
        'medition',
        'url',
        'platform',
        'duration',
        'size',
        'available',
        'position',
        'type_id',
        'course_id',
        'chapter_id',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'available' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('audio')->singleFile();
        $this->addMediaCollection('image')->singleFile();
        $this->addMediaCollection('zip')->singleFile();
        $this->addMediaCollection('pdf')->singleFile();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->logFillable()->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");
    }

    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy('created_at', 'asc');
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

    public function scopeQuizzes($query)
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

    public function downloadableMedia()
    {
        $types = ['youtube', 'vimeo', 'upload', 'embed', 'lesson_pdf', 'lesson_audio'];

        return $this->morphMany('App\Models\Media', 'model')->whereNotIn('type', $types);
    }

    public function mediaVideo()
    {
        $types = ['youtube', 'vimeo', 'upload', 'embed'];

        return $this->morphOne('App\Models\Media', 'model')->whereIn('type', $types);
    }

    public function mediaPDF()
    {
        return $this->morphOne('App\Models\Media', 'model')->where('type', '=', 'lesson_pdf');
    }

    public function mediaAudio()
    {
        return $this->morphOne('App\Models\Media', 'model')->where('type', '=', 'lesson_audio');
    }

    public function isCompleted()
    {
        return $this->chapterStudents()->where('user_id', auth()->id())->exists();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course', 'course_id', 'id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\CourseChapter', 'chapter_id', 'id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\CourseType', 'type_id', 'id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course', 'course_id', 'id');
    }

    public function lesson(): HasOne
    {
        return $this->hasOne('App\Models\Course\CourseLesson', 'id');
    }

    public function quiztopic(): HasOne
    {
        return $this->hasOne('App\Models\Quiz\QuizTopic', 'lesson_id', 'id');
    }
}
