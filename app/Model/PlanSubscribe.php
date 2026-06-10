<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanSubscribe extends Model
{
    use HasFactory;

    protected $connection = 'mysql_second';

    protected $table = 'plan_subscription';

    protected $fillable = [
        'plan_id',
        'user_id',
        'order_id',
        'transaction_id',
        'payment_method',
        'total_amount',
        'currency',
        'currency_icon',
        'duration',
        'duration_type',
        'enroll_start',
        'enroll_expire',
    ];

    public function plans()
    {
        return $this->belongsTo('App\Model\InstructorPlan', 'plan_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }
}
