<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderActivity extends Model
{
    use HasFactory;

    protected $table = 'orders_activity';

    protected $fillable = [
        'slack',
        'item_type',
        'item_id',
        'id_type',
        'relation_id',
        'order_id',
        'course_id',
        'enterprise_id',
        'distributor_id',
        'invoiced',
        'invoiced_at',
        'created_at',
        'updated_at',
    ];

    public function scopeInvoiced($query)
    {
        return $query->where('invoiced', 1);
    }

    public function scopePending($query)
    {
        return $query->where('invoiced', 0);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo('App\Models\Order\Order', 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course', 'item_id');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo('App\Models\Distributor\Distributor', 'distributor_id');
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo('App\Models\Enterprise\Enterprise', 'enterprise_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'staff_id');
    }

    public function scopeDate($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end])->where('invoiced', 0);
    }
}
