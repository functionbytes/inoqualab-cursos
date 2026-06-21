<?php

namespace App\Models\Mailer;

use App\Enums\EndpointLogStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailerEndpoint extends Model
{
    use SoftDeletes;

    protected $table = 'mailer_endpoints';

    protected $fillable = [
        'name', 'slug', 'source', 'type', 'description',
        'mailer_template_id', 'expected_variables', 'required_variables',
        'variable_mappings', 'is_active', 'api_token',
        'requests_count', 'last_request_at',
    ];

    protected function casts(): array
    {
        return [
            'expected_variables' => 'array',
            'required_variables' => 'array',
            'variable_mappings' => 'array',
            'is_active' => 'boolean',
            'last_request_at' => 'datetime',
        ];
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->api_token)) {
                $model->api_token = self::generateToken();
            }
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MailerTemplate::class, 'mailer_template_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MailerEndpointLog::class);
    }

    public function successLogs(): HasMany
    {
        return $this->logs()->where('status', EndpointLogStatus::Success->value);
    }

    public function failedLogs(): HasMany
    {
        return $this->logs()->where('status', EndpointLogStatus::Failed->value);
    }

    public function successRate(?int $successCount = null, ?int $totalCount = null): float
    {
        $total = $totalCount ?? $this->requests_count ?? 0;
        if ($total <= 0) {
            return 0.0;
        }
        if ($successCount === null) {
            $successCount = $this->success_logs_count ?? $this->successLogs()->count();
        }

        return round(($successCount / $total) * 100, 1);
    }

    public function getSuccessRateAttribute(): float
    {
        return $this->successRate();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }
}
