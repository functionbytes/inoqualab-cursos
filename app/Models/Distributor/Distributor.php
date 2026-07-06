<?php

namespace App\Models\Distributor;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Distributor extends Model
{
    use HasFactory,
        HasFinders, LogsActivity, SoftDeletes;

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $table = 'distributors';

    protected $fillable = [
        'slack',
        'title',
        'address',
        'cellphone',
        'nit',
        'leading',
        'supporting',
        'available',
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

    public function scopeAvailable($query)
    {
        return $query->where('available', 1);
    }

    public function scopeDescending($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAscending($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeDistributorOrdersByDate($query, $distributor, $start, $end)
    {
        return $query->whereHas('orders', function ($q) use ($start, $end, $distributor) {
            $q->whereBetween('created_at', [$start, $end])->where('invoiced', 0)->where('id_type', $distributor);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {

        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");

    }

    public function enterprises(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Enterprise\Enterprise', 'distributor_enterprises')->withPivot('distributor_id')->orderBy('enterprises.created_at', 'desc');
    }

    public function staffs(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User', 'distributor_staff')->withPivot('distributor_id')->orderBy('users.created_at', 'desc');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Course\Course', 'distributor_courses')->withPivot('distributor_id');
    }

    public function rates(): HasMany
    {
        return $this->hasMany('App\Models\Distributor\DistributorCourse', 'distributor_id')->orderBy('created_at', 'desc');
    }

    public function orders(): HasMany
    {
        return $this->hasMany('App\Models\Order\OrderActivity')->orderBy('created_at', 'desc');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany('App\Models\Invoice\Invoice', 'distributor_id')->orderBy('created_at', 'desc');
    }

    public function ordersActitity(): HasMany
    {
        return $this->hasMany('App\Models\Order\OrderActivity', 'distributor_id')->orderBy('created_at', 'desc');
    }

    // Modelo Distributor
    public function ordersActititys()
    {
        return $this->hasManyThrough(
            'App\Models\Order\Order',  // Modelo final al que queremos acceder (Order)
            'App\Models\Order\OrderActivity',  // Modelo intermedio (OrderActivity)
            'distributor_id',  // Clave foránea en la tabla intermedia (OrderActivity)
            'id',  // Clave primaria en la tabla destino (Order)
            'id',  // Clave primaria en la tabla origen (Distributor)
            'order_id'  // Clave foránea en la tabla intermedia (OrderActivity)
        );
    }
}
