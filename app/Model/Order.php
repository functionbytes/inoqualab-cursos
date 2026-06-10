<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'orders';

    protected $fillable = [
        'slack',
        'number',
        'total',
        'subtotal',
        'discount',
        'transaction',
        'enroll_start',
        'enroll_expire',
        'payment_at',
        'available',
        'method_id',
        'course_id',
        'user_id',
        'condition_id',
        'created_at',
        'updated_at',
    ];

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function scopeSlack($query, $slack)
    {
        return $query->where('slack', $slack)->first();
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
        return $query->where('user_id', $user)->get();
    }

    public function scopeCompleteds($query, $user)
    {

        $end = Carbon::parse()->endOfDay();

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

        $end = Carbon::parse()->endOfDay();

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
        $end = Carbon::parse()->endOfDay();

        return DB::table('orders')
            ->join('courses', function ($join) {
                $join->on('courses.id', '=', 'orders.course_id');
            })->join('course_user', function ($join) {
                $join->on('course_user.order_id', '=', 'orders.id');
            })->whereDate('orders.enroll_expire', '>=', $end)->where('course_user.culminated', '=', 0)->where('orders.user_id', '=', $user)->select(
                'orders.id',
            )->get();

    }

    public function scopeReport($query, $enterprise, $course, $start, $end)
    {

        return DB::table('orders')
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'orders.user_id');
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

    public function scopeEnroll($query, $user)
    {
        return $query->where('user_id', $user)->where('status', '1')->where('condition_id', '4')->get();
    }

    public function scopeExpire($query, $user)
    {
        return $query->where('user_id', $user)->where('status', '0')->where('condition_id', '4')->get();
    }

    public function scopeUsers($query, $enterprise, $course)
    {
        return DB::table('users')
            ->join('enterprise_user', function ($join) {
                $join->on('users.id', '=', 'enterprise_user.user_id');
            })->where('enterprise_user.enterprise_id', '=', $enterprise)
            ->join('orders', function ($join) {
                $join->on('orders.user_id', '=', 'users.id');
            })->where('orders.course_id', '=', $course)
            ->select(
                'users.*',
                'orders.enroll_expire',
                'orders.enroll_start',
            )->get();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo('App\Model\Method', 'method_id', 'id');
    }

    public function coursing(): HasOne
    {
        return $this->hasOne('App\Model\CourseUser', 'order_id');
    }

    public function quizs(): HasMany
    {
        return $this->hasMany('App\Model\Quiz', 'order_id')->orderBy('created_at', 'desc');
    }

    public function progress(): HasMany
    {
        return $this->hasMany('App\Model\CourseProgress', 'order_id')->orderBy('created_at', 'desc');
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo('App\Model\Condition', 'condition_id', 'id');
    }

    public function exam(): HasOne
    {
        return $this->hasOne('App\Model\Exam', 'order_id', 'id');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne('App\Model\Certificate', 'order_id', 'id');
    }
}
