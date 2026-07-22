<?php

namespace App\Models\Users;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Certificate extends Model implements HasMedia
{
    use HasFactory,
        HasFinders, InteractsWithMedia, LogsActivity;

    protected $table = 'certificates';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    // OJO: la tabla certificates NO tiene columna order_id; y sin inscription_id
    // aquí, Certificate::create() (ExamController) la descartaba en silencio y el
    // certificado quedaba huérfano de su inscripción (rompe Inscription->certificate).
    protected $fillable = [
        'slack',
        'user_id',
        'course_id',
        'inscription_id',
        'exam_id',
        'certifier_id',
        'certification_id',
        'start_at',
        'end_at',
        'created_at',
        'updated_at',
    ];

    public function isUserCertified()
    {
        $status = false;
        $certified = auth()->user()->certificates()->where('course_id', '=', $this->id)->first();
        if ($certified != null) {
            $status = true;
        }

        return $status;
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->logFillable()->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course', 'course_id', 'id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo('App\Models\Exam\Exam', 'exam_id', 'id');
    }

    public function certifier(): BelongsTo
    {
        return $this->belongsTo('App\Models\Certifier', 'certifier_id', 'id');
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo('App\Models\Certification', 'certification_id', 'id');
    }
}
