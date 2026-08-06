<?php

namespace App\Http\Seo;

use App\Models\Seo\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Agrega relación y accessors SEO a un modelo Eloquent.
 *
 * En listados con accessors SEO, hacer eager-load: ->with('seoMeta')
 * loadedSeoMeta() evita N+1: solo lee la relación si ya fue cargada.
 */
trait HasSeo
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    protected function loadedSeoMeta(): ?SeoMeta
    {
        return $this->relationLoaded('seoMeta') ? $this->getRelation('seoMeta') : null;
    }

    public function hasSeoMeta(): bool
    {
        return $this->seoMeta()->exists();
    }

    public function isIndexable(): bool
    {
        $robots = $this->loadedSeoMeta()?->robots ?? 'index,follow';

        return ! str_contains($robots, 'noindex');
    }

    public function isFollowable(): bool
    {
        // "noindex,nofollow" CONTIENE la subcadena "follow", así que un
        // str_contains simple daba true justo cuando el editor pedía "nofollow".
        $robots = strtolower($this->loadedSeoMeta()?->robots ?? 'index,follow');

        return str_contains($robots, 'follow') && ! str_contains($robots, 'nofollow');
    }

    public function updateSeoMeta(array $data): SeoMeta
    {
        return $this->seoMeta()->updateOrCreate(
            ['seoable_type' => static::class, 'seoable_id' => $this->getKey()],
            $data
        );
    }

    public function deleteSeoMeta(): ?bool
    {
        return $this->seoMeta()->delete();
    }

    // ── Accessors con fallback al modelo ──────────────────────────────────

    public function getSeoTitleAttribute(): string
    {
        return $this->loadedSeoMeta()?->title
            ?? $this->meta_title
            ?? $this->title
            ?? setting('meta_title', config('app.name'));
    }

    public function getSeoDescriptionAttribute(): string
    {
        return $this->loadedSeoMeta()?->description
            ?? $this->meta_description
            ?? $this->short
            ?? $this->description
            ?? setting('meta_description', '');
    }

    public function getOgTitleAttribute(): string
    {
        $meta = $this->loadedSeoMeta();

        return $meta?->og_title ?? $meta?->title ?? $this->seo_title;
    }

    public function getOgDescriptionAttribute(): string
    {
        $meta = $this->loadedSeoMeta();

        return $meta?->og_description ?? $meta?->description ?? $this->seo_description;
    }

    public function getOgImageAttribute(): string
    {
        return $this->loadedSeoMeta()?->og_image ?? setting('seo_og_image_default', getMeta());
    }

    public function getCanonicalUrlAttribute(): string
    {
        return $this->loadedSeoMeta()?->canonical_url ?? $this->url ?? url()->current();
    }

    public function getRobotsAttribute(): string
    {
        return $this->loadedSeoMeta()?->robots ?? 'index,follow';
    }
}
