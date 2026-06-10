<?php

namespace App\Model;

use App\Models\CourseClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class CourseProgress extends Model
{
    protected $table = 'course_progress';

    protected $connection = 'mysql_second';

    protected $fillable = [
        'user_id',
        'course_id',
        'chapter_id',
        'class_id',
        'order_id',
        'culminated',
        'created_at',
        'updated_at',
    ];

    public function scopeClass($query, $class)
    {
        return $query->where('class_id', $class);
    }

    public function scopeOrder($query, $order)
    {
        return $query->where('order_id', $order);
    }

    public function scopeChapters($query, $id)
    {
        return $query->where('chapter_id', $id);
    }

    public function scopeValidate($query, $class, $order, $user)
    {
        return $query->where('class_id', $class)
            ->where('order_id', $order)
            ->where('user_id', $user)
            ->where('culminated', 1)
            ->exists();
    }

    public function scopePrevNext($query, $class, $direction = 'prev')
    {
        $classing = CourseClass::id($class);
        $reversedIds = new Collection;

        $reversedIds = $classing->course->chapter->flatMap(function ($chapter) {
            return $chapter->lessons->pluck('id');
        });

        $position = $reversedIds->search($classing->id);

        if ($direction == 'prev') {
            $position--;
        } else {
            $position++;
        }

        if ($position == $reversedIds->count() || $position < 0) {
            return true;
        }

        return CourseClass::id($reversedIds->get($position));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo('App\Model\CourseChapter', 'chapter_id', 'id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo('App\Model\CourseClass', 'class_id', 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo('App\Model\Order', 'order_id', 'id');
    }
}
