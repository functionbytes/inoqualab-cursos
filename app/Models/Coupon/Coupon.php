<?php

namespace App\Models\Coupon;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Coupon extends Model
{
    use HasFactory,
        HasFinders, LogsActivity;

    protected $table = 'coupons';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $fillable = [
        'slack',
        'title',
        'description',
        'code',
        'type',
        'amount',
        'min_price',
        'start_date',
        'end_date',
        'limit',
        'usage',
        'bundle_ids',
        'course_ids',
        'available',
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

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    public function scopeSlack($query, $slack)
    {
        $model = $query->where('slack', $slack)->first();
        abort_unless($model !== null, 404);

        return $model;
    }

    public function orders(): HasMany
    {
        return $this->hasMany('App\Models\Order\Order', 'coupon_id', 'id');
    }
}
