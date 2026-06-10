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

    public function scopeTariff($query, $course, $distributor)
    {

        return $query->where('course_id', $course)
            ->where('distributor_id', $distributor)
            ->first()->price; // Solo traer el parámetro price
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
