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
use Illuminate\Support\Facades\DB;
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
        'subtotal',
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

    public static function scopeTotal($query, $method, $condition)
    {
        return $query->where('method_id', $method)->where('condition_id', $condition)->sum('total');
    }

    public static function finds($user, $course)
    {
        return Order::where('user_id', $user)->where('course_id', $course)->get();
    }

    public function scopeValidate($query, $user, $course)
    {
        return $query->where('user_id', $user)->where('course_id', $course)->first();
    }

    public function scopeValidates($query, $user, $course)
    {
        return $query->where('user_id', $user)->where('course_id', $course)->get();
    }

    public function scopeActions($query, $method, $condition)
    {
        return $query->where('method_id', $method)->where('condition_id', $condition)->get();
    }

    public function scopePurchases($query, $user)
    {
        return $query->where('user_id', $user);
    }

    public function scopeCompleteds($query, $user)
    {
        $end = Carbon::now()->endOfDay();

        return DB::table('orders')
            ->join('courses', function ($join) {
                $join->on('courses.id', '=', 'orders.course_id');
            })->join('course_user', function ($join) {
                $join->on('course_user.order_id', '=', 'orders.id');
            })->where('course_user.culminated', '=', 1)->where('orders.user_id', '=', $user)->select(
                'orders.id',
            )->get();

    }

    public function scopeExpires($query, $user)
    {

        $end = Carbon::now()->endOfDay();

        return DB::table('orders')
            ->join('courses', function ($join) {
                $join->on('courses.id', '=', 'orders.course_id');
            })->join('course_user', function ($join) {
                $join->on('course_user.order_id', '=', 'orders.id');
            })->whereDate('orders.enroll_expire', '>=', $end)->where('course_user.culminated', '=', 0)->where('orders.user_id', '=', $user)->select(
                'orders.id',
            )->get();

    }

    public function scopeEarrings($query, $user)
    {
        $end = Carbon::now()->endOfDay();

        return DB::table('orders')
            ->join('courses', function ($join) {
                $join->on('courses.id', '=', 'orders.course_id');
            })->join('course_user', function ($join) {
                $join->on('course_user.order_id', '=', 'orders.id');
            })->whereDate('orders.enroll_expire', '>=', $end)->where('course_user.culminated', '=', 0)->where('orders.user_id', '=', $user)->select(
                'orders.id',
            )->get();

    }

    public function scopeByEnterprise($query, $enterpriseId)
    {
        return $query->join('users', 'users.id', '=', 'orders.user_id')
            ->join('enterprise_user', 'users.id', '=', 'enterprise_user.user_id')
            ->where('enterprise_user.enterprise_id', $enterpriseId)
            ->select('orders.*');
    }

    public function scopeReport($query, $enterprise, $course, $start, $end)
    {

        return DB::table('orders')
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'orders.user_id');
            })->join('enterprise_user', function ($join) {
                $join->on('enterprise_user.user_id', '=', 'users.id');
            })->join('course_user', function ($join) {
                $join->on('course_user.order_id', '=', 'orders.id');
            })->where('enterprise_user.enterprise_id', '=', $enterprise)
            ->where('orders.course_id', '=', $course)
            ->whereBetween('orders.payment_at', [$start, $end])
            ->select(
                'users.slack',
                'users.firstname',
                'users.lastname',
                'users.available',
                'users.cellphone',
                'users.address',
                'users.identification',
                'users.email',
                'course_user.id',
                'course_user.order_id',
                'course_user.culminated',
                'course_user.culminated_at',
                'course_user.percent',
                'course_user.updated_at',
                'course_user.created_at'
            )->orderBy('culminated_at', 'desc');

    }

    public function items(): HasMany
    {
        return $this->hasMany('App\Models\Order\OrderItem');
    }

    public function invoice(): HasMany
    {
        return $this->hasMany('App\Models\Invoice\Invoice');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo('App\Models\Distributor\Distributor', 'distributor_id');
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
