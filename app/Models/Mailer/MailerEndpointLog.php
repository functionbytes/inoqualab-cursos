<?php

namespace App\Models\Mailer;

use App\Enums\EndpointLogStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailerEndpointLog extends Model
{
    protected $table = 'mailer_endpoint_logs';

    protected $fillable = [
        'mailer_endpoint_id', 'payload', 'status', 'error_message',
        'recipient_email', 'mailer_subject', 'sent_at', 'job_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => EndpointLogStatus::class,
            'payload' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(MailerEndpoint::class, 'mailer_endpoint_id');
    }

    public function scopeSuccess(Builder $query): void
    {
        $query->where('status', EndpointLogStatus::Success->value);
    }

    public function scopeFailed(Builder $query): void
    {
        $query->where('status', EndpointLogStatus::Failed->value);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', EndpointLogStatus::Pending->value);
    }

    public function scopeSearchEmail(Builder $query, string $email): void
    {
        $query->where('recipient_email', 'like', "%{$email}%");
    }

    public function scopePeriod(Builder $query, string $period): void
    {
        $query->where('created_at', '>=', match ($period) {
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subYear(),
        });
    }

    public function scopeOlderThan(Builder $query, int $days): void
    {
        $query->where('created_at', '<', now()->subDays($days));
    }
}
