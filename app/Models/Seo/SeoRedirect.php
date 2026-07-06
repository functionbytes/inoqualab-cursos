<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SeoRedirect extends Model
{
    public const CACHE_KEY = 'seo.redirects.all';

    protected $table = 'seo_redirects';

    protected $fillable = [
        'source_path',
        'target_path',
        'status_code',
        'is_regex',
        'is_wildcard',
        'is_active',
        'hits_count',
        'note',
    ];

    public function casts(): array
    {
        return [
            'is_regex' => 'boolean',
            'is_wildcard' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $redirect) {
            $redirect->source_path = static::normalizeSourcePath(
                $redirect->source_path,
                (bool) $redirect->is_regex,
                (bool) $redirect->is_wildcard
            );

            $redirect->target_path = static::normalizeTargetPath($redirect->target_path);
        });

        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    /**
     * Normaliza el source_path tal como quedará persistido. Se expone como método
     * público porque también lo usa SeoRedirectController para anticipar, ANTES de
     * guardar, si un valor formaría un bucle/ciclo (ver RedirectChainDetector).
     */
    public static function normalizeSourcePath(string $path, bool $isRegex = false, bool $isWildcard = false): string
    {
        if ($isRegex || $isWildcard) {
            return $path;
        }

        $path = strtolower(trim($path));

        return str_starts_with($path, '/') ? $path : '/'.$path;
    }

    /** Normaliza el target_path tal como quedará persistido. */
    public static function normalizeTargetPath(string $path): string
    {
        if (str_starts_with($path, '/') || filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return '/'.$path;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('source_path', 'like', "%{$term}%")
                ->orWhere('target_path', 'like', "%{$term}%");
        });
    }

    public static function findBySourcePath(string $path): ?static
    {
        return static::query()
            ->active()
            ->where('is_regex', false)
            ->where('is_wildcard', false)
            ->where('source_path', $path)
            ->first();
    }

    public static function cachedAll(): Collection
    {
        return Cache::remember(self::CACHE_KEY, 3600, fn () => static::active()->get());
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
