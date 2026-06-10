<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $table = 'seo_metas';

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'title',
        'description',
        'keywords',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'canonical_url',
        'robots',
        'schema_type',
        'schema_custom',
        'seo_score',
        'seo_grade',
        'seo_audited_at',
        'target_keyword',
    ];

    public function casts(): array
    {
        return [
            'schema_custom' => 'array',
            'seo_score' => 'integer',
            'seo_audited_at' => 'datetime',
        ];
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isIndexable(): bool
    {
        return ! str_contains((string) $this->robots, 'noindex');
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('seoable_type', $type);
    }

    public function scopeByScore($query)
    {
        return $query->orderByDesc('seo_score');
    }
}
