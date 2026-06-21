<?php

namespace App\Models\Invoice;

use App\Models\Concerns\HasFinders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasFactory,
        HasFinders, LogsActivity;

    protected $table = 'invoices';

    protected $fillable = [
        'slack',
        'number',
        'reference',
        'subtotal',
        'date',
        'due_date',
        'distributor_id',
        'method_id',
        'condition_id',
        'notes',
        'total_discount_amount',
        'total_after_discount',
        'total_before_discount',
        'total_tax_amount',
        'total_invoices_amount',
        'available',
        'from_at',
        'to_at',
        'payment_at',
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

    public function distributor(): BelongsTo
    {
        return $this->belongsTo('App\Models\Distributor\Distributor', 'distributor_id', 'id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo('App\Models\Invoice\InvoiceMethod', 'method_id', 'id');
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo('App\Models\Invoice\InvoiceCondition', 'condition_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany('App\Models\Invoice\InvoiceItem');
    }

    public function details(): HasMany
    {
        return $this->hasMany('App\Models\Invoice\InvoiceDetails');
    }
}
