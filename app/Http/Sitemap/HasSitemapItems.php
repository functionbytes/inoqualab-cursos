<?php

namespace App\Http\Sitemap;

use Illuminate\Database\Eloquent\Collection;

trait HasSitemapItems
{
    protected static string $sitemapStatusColumn = 'available';

    protected static string $sitemapStatusValue = '1';

    public static function getSitemapItems(): Collection
    {
        return static::query()
            ->where(static::$sitemapStatusColumn, static::$sitemapStatusValue)
            ->orderByDesc('updated_at')
            ->limit(5000)
            ->get()
            ->filter(fn ($item) => ! $item->excludeFromSitemap())
            ->values();
    }

    public function excludeFromSitemap(): bool
    {
        return false;
    }

    public function getSitemapPriorityAttribute(): string
    {
        return '0.8';
    }

    public function getSitemapChangefreqAttribute(): string
    {
        return 'weekly';
    }

    public function getUrlAttribute(): string
    {
        return url('/');
    }
}
