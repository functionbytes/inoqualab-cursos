<?php

namespace App\Models\Course;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sin LogsActivity a propósito: auditar el avance lección a lección generaba
 * 599k filas en activity_log (el mayor emisor de la tabla, ~770 MB en total)
 * duplicando lo que ya guarda esta misma tabla. Ninguna interfaz lo consultaba
 * — ActivitysController solo filtra por Enterprise/User/Distributor/Order/Invoice.
 */
class CourseProgress extends Model
{
    use HasFactory,
        HasFinders;

    protected $table = 'course_progress';

    protected $fillable = [
        'user_id',
        'course_id',
        'chapter_id',
        'lesson_id',
        'inscription_id',
        'culminated',
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

    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeLessons($query, $class)
    {
        return $query->where('lesson_id', $class);
    }

    public function scopeInscription($query, $inscription)
    {
        return $query->where('inscription_id', $inscription);
    }

    public function scopeChapters($query, $id)
    {
        return $query->where('chapter_id', $id);
    }

    public function scopeValidate($query, $class, $inscription, $user)
    {
        return $query->where('lesson_id', $class)
            ->where('inscription_id', $inscription)
            ->where('user_id', $user)
            ->where('culminated', 1)
            ->exists();
    }

    public function scopeReturn($query, $class, $inscription, $user)
    {
        return $query->where('lesson_id', $class)
            ->where('inscription_id', $inscription)
            ->where('user_id', $user)
            ->where('culminated', 1)
            ->first();
    }

    public function scopePrevNext($query, $class, $direction = 'prev')
    {

        $classing = CourseLesson::findOrFail($class);

        $lessonIds = $classing->course->chapters->flatMap(function ($chapter) {
            return $chapter->lessons->pluck('id');
        });

        $position = $lessonIds->search($classing->id);
        $newPosition = $direction === 'prev' ? $position - 1 : $position + 1;

        if ($newPosition < 0 || $newPosition >= $lessonIds->count()) {
            return 'true';
        }

        return CourseLesson::find($lessonIds[$newPosition]);
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

    public function lesson(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\CourseLesson', 'lesson_id', 'id');
    }

    public function inscription(): BelongsTo
    {
        return $this->belongsTo('App\Models\Inscription', 'inscription_id', 'id');
    }
}
