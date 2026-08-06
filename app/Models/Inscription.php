<?php

namespace App\Models;

use App\Models\Concerns\HasFinders;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Inscription extends Model
{
    // La tabla ya tenía `deleted_at` sin que el modelo usara SoftDeletes:
    // destroy() (UsersCoursesController) hacía HARD DELETE real en vez de
    // soft-delete, perdiendo el historial de matrícula de forma irreversible.
    use HasFactory,
        HasFinders, LogsActivity, SoftDeletes;

    protected $table = 'inscriptions';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $fillable = [
        'slack',
        'user_id',
        'course_id',
        'inscription_id',
        'order_id',
        'percent',
        'enroll_start',
        'enroll_expire',
        'enroll_culminated',
        'culminated',
        'expire',
        'created_at',
        'updated_at',
    ];

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

    public function scopeExistingInscription($query, $userId, $courseId)
    {
        $monthsRange = setting('time_inscription') ?: 3;
        $startDate = Carbon::now()->setTimezone('America/Bogota');
        $endDate = $startDate->copy()->addMonths($monthsRange);

        return $query->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('enroll_start', [$startDate, $endDate])
                    ->orWhereBetween('enroll_expire', [$startDate, $endDate]);
            });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course', 'course_id', 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo('App\Models\Order\Order', 'order_id', 'id');
    }

    public function quizs(): HasMany
    {
        return $this->hasMany('App\Models\Quiz\Quiz', 'inscription_id')->orderBy('created_at', 'desc');
    }

    public function progress(): HasMany
    {
        return $this->hasMany('App\Models\Course\CourseProgress', 'inscription_id')->orderBy('created_at', 'desc');
    }

    public function exam(): HasOne
    {
        return $this->hasOne('App\Models\Exam\Exam', 'inscription_id', 'id');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne('App\Models\Users\Certificate', 'inscription_id', 'id');
    }
}
