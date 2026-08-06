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
        'locale',
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
        'gsc_clicks',
        'gsc_impressions',
        'gsc_position',
        'gsc_updated_at',
    ];

    public function casts(): array
    {
        return [
            'schema_custom' => 'array',
            'seo_score' => 'integer',
            'seo_audited_at' => 'datetime',
            'gsc_clicks' => 'integer',
            'gsc_impressions' => 'integer',
            'gsc_position' => 'float',
            'gsc_updated_at' => 'datetime',
        ];
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getShortTypeAttribute(): string
    {
        return class_basename($this->seoable_type ?? '');
    }

    public function isIndexable(): bool
    {
        return ! str_contains(strtolower((string) $this->robots), 'noindex');
    }

    public function isFollowable(): bool
    {
        // "noindex,nofollow" CONTIENE la subcadena "follow", así que un
        // str_contains simple daba true justo cuando el editor pedía "nofollow".
        $robots = strtolower((string) $this->robots);

        return str_contains($robots, 'follow') && ! str_contains($robots, 'nofollow');
    }

    public function hasAbTest(): bool
    {
        return (bool) ($this->title_b || $this->description_b);
    }

    public function getActiveTitle(): string
    {
        if ($this->ab_winner === 'b' && $this->title_b) {
            return $this->title_b;
        }

        if (! $this->ab_winner && $this->title_b) {
            return ($this->ab_impressions_a ?? 0) <= ($this->ab_impressions_b ?? 0)
                ? ($this->title ?? '')
                : ($this->title_b ?? $this->title ?? '');
        }

        return $this->title ?? '';
    }

    public function getActiveDescription(): string
    {
        if ($this->ab_winner === 'b' && $this->description_b) {
            return $this->description_b;
        }

        if (! $this->ab_winner && $this->description_b) {
            return ($this->ab_impressions_a ?? 0) <= ($this->ab_impressions_b ?? 0)
                ? ($this->description ?? '')
                : ($this->description_b ?? $this->description ?? '');
        }

        return $this->description ?? '';
    }

    public function getActiveVariant(): string
    {
        if ($this->ab_winner) {
            return $this->ab_winner;
        }

        return ($this->ab_impressions_a ?? 0) <= ($this->ab_impressions_b ?? 0) ? 'a' : 'b';
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('seoable_type', $type);
    }

    public function scopeByScore($query, string $direction = 'desc')
    {
        return $query->orderBy('seo_score', $direction);
    }

    public function scopeWithRobots($query, string $robots)
    {
        return $query->where('robots', $robots);
    }
}
