<?php

namespace App\Models\Seo;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SeoWebVital extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'seo_web_vitals';

    public const THRESHOLDS = [
        'LCP' => ['good' => 2500, 'poor' => 4000],
        'INP' => ['good' => 200, 'poor' => 500],
        'CLS' => ['good' => 0.1, 'poor' => 0.25],
        'FCP' => ['good' => 1800, 'poor' => 3000],
        'TTFB' => ['good' => 800, 'poor' => 1800],
    ];

    protected $fillable = [
        'url',
        'url_path',
        'metric',
        'value',
        'rating',
        'device',
        'connection',
        'navigation_type',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:3',
            'captured_at' => 'datetime',
        ];
    }

    public function scopeForMetric(Builder $query, string $metric): Builder
    {
        return $query->where('metric', strtoupper($metric));
    }

    public function scopeSince(Builder $query, DateTimeInterface|string $since): Builder
    {
        return $query->where('captured_at', '>=', $since);
    }

    public function scopeForUrlPath(Builder $query, string $path): Builder
    {
        return $query->where('url_path', $path);
    }

    public static function rate(string $metric, float $value): string
    {
        $thresholds = self::THRESHOLDS[$metric] ?? null;

        if ($thresholds === null) {
            return 'unknown';
        }

        if ($value <= $thresholds['good']) {
            return 'good';
        }

        if ($value <= $thresholds['poor']) {
            return 'needs-improvement';
        }

        return 'poor';
    }

    public static function p75(string $metric, ?string $urlPath = null, int $lastDays = 28): ?float
    {
        $query = static::query()
            ->forMetric($metric)
            ->since(now()->subDays($lastDays));

        if ($urlPath !== null) {
            $query->forUrlPath($urlPath);
        }

        $count = (int) $query->count();

        if ($count === 0) {
            return null;
        }

        $offset = max(0, min((int) floor($count * 0.75), $count - 1));
        $value = $query->orderBy('value')->offset($offset)->limit(1)->value('value');

        return $value === null ? null : (float) $value;
    }
}
