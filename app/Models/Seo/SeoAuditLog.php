<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoAuditLog extends Model
{
    protected $table = 'seo_audit_logs';

    protected $fillable = [
        'seo_meta_id',
        'url',
        'score',
        'grade',
        'issues_count',
        'issues',
        'passed_count',
        'audited_at',
    ];

    protected function casts(): array
    {
        return [
            'issues' => 'array',
            'audited_at' => 'datetime',
            'score' => 'integer',
            'issues_count' => 'integer',
            'passed_count' => 'integer',
        ];
    }

    public function seoMeta(): BelongsTo
    {
        return $this->belongsTo(SeoMeta::class, 'seo_meta_id');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('audited_at', 'desc');
    }

    public function scopeForMeta(Builder $query, int $metaId): Builder
    {
        return $query->where('seo_meta_id', $metaId);
    }
}
