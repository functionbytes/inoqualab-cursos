<?php

namespace App\Models\Order;

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

class Order extends Model
{
    use HasFactory,
        HasFinders, LogsActivity, SoftDeletes;

    protected $table = 'orders';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $fillable = [
        'slack',
        'number',
        'reference',
        'distributor_id',
        'user_id',
        'type_id',
        'method_id',
        'condition_id',
        'coupon_id',
        'payment_at',
        'transaction',
        'notes',
        'total_discount_amount',
        'total_after_discount',
        'total_tax_amount',
        'total_order_amount',
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

    public function scopeActions($query, $method, $condition)
    {
        return $query->where('method_id', $method)->where('condition_id', $condition)->get();
    }

    public function scopePurchases($query, $user)
    {
        return $query->where('user_id', $user);
    }

    public function scopeByEnterprise($query, $enterpriseId)
    {
        return $query->join('users', 'users.id', '=', 'orders.user_id')
            ->join('enterprise_user', 'users.id', '=', 'enterprise_user.user_id')
            ->where('enterprise_user.enterprise_id', $enterpriseId)
            ->select('orders.*');
    }

    public function items(): HasMany
    {
        return $this->hasMany('App\Models\Order\OrderItem');
    }

    public function invoice(): HasMany
    {
        return $this->hasMany('App\Models\Invoice\Invoice');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo('App\Models\Order\OrderMethod', 'method_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo('App\Models\Order\OrderType', 'type_id');
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo('App\Models\Order\OrderCondition', 'condition_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo('App\Models\Coupon\Coupon', 'coupon_id');
    }

    public function activity(): HasOne
    {
        return $this->hasOne('App\Models\Order\OrderActivity', 'order_id');
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany('App\Models\Inscription', 'order_id');
    }

    public function inscription(): HasOne
    {
        return $this->hasOne('App\Models\Inscription', 'order_id');
    }

    public static function filterOrders($filters)
    {
        $query = self::query()->select('orders.*', 'orders.slack'); // Asegurar que orders.slack esté presente

        if (! empty($filters['range'])) {
            $date = explode(' - ', $filters['range']);
            if (count($date) === 2) {
                $start = Carbon::parse($date[0])->startOfDay();
                $end = Carbon::parse($date[1])->endOfDay();
                $query->whereBetween('orders.created_at', [$start, $end]);
            }
        }

        if (! empty($filters['distributor'])) {
            $query->join('orders_activity', function ($join) {
                $join->on('orders.id', '=', 'orders_activity.order_id');
            })->where('orders_activity.distributor_id', $filters['distributor']);
        }

        if (! empty($filters['enterprise'])) {
            $query->join('users', 'users.id', '=', 'orders.user_id')
                ->join('enterprise_user', 'users.id', '=', 'enterprise_user.user_id')
                ->where('enterprise_user.enterprise_id', $filters['enterprise']);
        }

        if (! empty($filters['method'])) {
            $query->where('orders.method_id', $filters['method']);
        }

        if (! empty($filters['condition'])) {
            $query->where('orders.condition_id', $filters['condition']);
        }

        if (! empty($filters['type'])) {
            $query->where('orders.type_id', $filters['type']);
        }

        $query->orderByDesc('orders.created_at')
            ->with(['user', 'condition', 'method', 'type']); // evita N+1 en la vista de resumen

        return $query->paginate(paginationNumber());
    }
}
