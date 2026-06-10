<?php

namespace App\Models\Mail;

use App\Models\Concerns\HasFinders;
use App\Models\Enterprise\Enterprise;
use App\Models\Order\Order;
use App\Models\User;
use Database\Factories\Mail\IncomingMailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class IncomingMail extends Model
{
    use HasFactory,
        HasFinders, LogsActivity;

    protected $table = 'incoming_mails';

    protected static $recordEvents = ['deleted', 'updated', 'created'];

    const STATUS_PENDING_REVIEW = 'pending_review';

    const STATUS_PROCESSED = 'processed';

    const STATUS_FAILED = 'failed';

    const STATUS_IGNORED = 'ignored';

    protected $fillable = [
        'slack',
        'message_id',
        'from',
        'subject',
        'received_at',
        'raw_body',
        'parsed_payload',
        'status',
        'confidence_score',
        'matched_enterprise_id',
        'order_id',
        'error_log',
        'notes',
        'assigned_to',
        'processed_at',
        'created_at',
        'updated_at',
    ];

    public function casts(): array
    {
        return [
            'parsed_payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
            'confidence_score' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable()
            ->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}");
    }

    protected static function newFactory(): IncomingMailFactory
    {
        return IncomingMailFactory::new();
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

    public function scopePendingReview($query)
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class, 'matched_enterprise_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
