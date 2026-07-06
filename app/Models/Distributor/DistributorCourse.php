<?php

namespace App\Models\Distributor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DistributorCourse extends Model
{
    use HasFactory , LogsActivity;

    protected $table = 'distributor_courses';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $fillable = [
        'course_id',
        'distributor_id',
        'created_at',
        'updated_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {

        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");

    }

    /**
     * Tarifa asignada al curso para ese distribuidor, o null si no existe.
     *
     * NO se implementa como `scopeTariff()`: los scopes locales de Eloquent
     * envuelven el valor devuelto en `$result ?? $this` (ver Builder::callScope()),
     * así que un `null` real se sustituye silenciosamente por el objeto Builder
     * — el mismo antipatrón que causó el bug de `enrollSimple()`. Un método
     * estático evita ese envoltorio y permite devolver null de verdad.
     */
    public static function tariff($course, $distributor): ?float
    {
        return static::where('course_id', $course)
            ->where('distributor_id', $distributor)
            ->first()?->price;
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo('App\Models\Distributor\Distributor', 'distributor_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course', 'course_id', 'id');
    }
}
