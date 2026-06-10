<?php

namespace App\Models\Account;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Account extends Model
{
    use HasFactory,
        HasFinders , LogsActivity;

    protected $table = 'certificates';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $fillable = [
        'slack',
        'distributor_id',
        'type_id',
        'description',
        'available',
        'created_at',
        'updated_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->logFillable()->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");

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

    public function distributor(): BelongsTo
    {
        return $this->belongsTo('App\Models\Distributor\Distributor', 'distributor_id', 'id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo('App\Models\Account\AccountType', 'type_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany('App\Models\Account\AccountItem');
    }
}
