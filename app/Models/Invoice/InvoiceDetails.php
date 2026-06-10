<?php

namespace App\Models\Invoice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InvoiceDetails extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'invoice_details';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    protected $fillable = [
        'slack',
        'order_id',
        'course_id',
        'invoice_id',
        'quantity',
        'amount',
        'created_at',
        'updated_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->logFillable()->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo('App\Models\Course\Course');
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo('App\Models\Enterprise\Enterprise');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo('App\Models\Invoice\Invoice');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo('App\Models\Order\Order');
    }
}
