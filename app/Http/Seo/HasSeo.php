<?php

namespace App\Http\Seo;

use App\Models\Seo\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function hasSeoMeta(): bool
    {
        return $this->seoMeta()->exists();
    }

    public function isIndexable(): bool
    {
        $robots = $this->seoMeta?->robots ?? 'index,follow';

        return ! str_contains($robots, 'noindex');
    }

    public function updateSeoMeta(array $data): SeoMeta
    {
        return $this->seoMeta()->updateOrCreate(
            ['seoable_type' => static::class, 'seoable_id' => $this->getKey()],
            $data
        );
    }

    public function deleteSeoMeta(): void
    {
        $this->seoMeta()->delete();
    }

    // Accessors con fallback a campos del propio modelo

    public function getSeoTitleAttribute(): string
    {
        return $this->seoMeta?->title
            ?? $this->meta_title
            ?? $this->title
            ?? setting('meta_title', config('app.name'));
    }

    public function getSeoDescriptionAttribute(): string
    {
        return $this->seoMeta?->description
            ?? $this->meta_description
            ?? $this->short
            ?? $this->description
            ?? setting('meta_description', '');
    }

    public function getOgTitleAttribute(): string
    {
        return $this->seoMeta?->og_title ?? $this->seo_title;
    }

    public function getOgDescriptionAttribute(): string
    {
        return $this->seoMeta?->og_description ?? $this->seo_description;
    }

    public function getOgImageAttribute(): string
    {
        return $this->seoMeta?->og_image ?? setting('seo_og_image_default', getMeta());
    }

    public function getCanonicalUrlAttribute(): string
    {
        return $this->seoMeta?->canonical_url ?? $this->url ?? url()->current();
    }

    public function getRobotsAttribute(): string
    {
        return $this->seoMeta?->robots ?? 'index,follow';
    }
}
