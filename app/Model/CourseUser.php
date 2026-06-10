<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseUser extends Model
{
    protected $connection = 'mysql_second';

    protected $table = 'course_user';

    protected $fillable = [
        'user_id',
        'course_id',
        'order_id',
        'percent',
        'culminated',
        'culminated_at',
        'created_at',
        'updated_at',
    ];

    public function scopeId($query, $id)
    {
        return $query->where('id', $id)->first();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Model\Course', 'course_id', 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo('App\Model\Order', 'order_id', 'id');
    }
}
