<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class SeoService
{
    private string $title = '';

    private string $description = '';

    private string $keywords = '';

    private string $canonical = '';

    private string $robots = 'index,follow';

    private string $ogTitle = '';

    private string $ogDescription = '';

    private string $ogImage = '';

    private string $ogType = 'website';

    private string $twitterCard = 'summary_large_image';

    private string $twitterTitle = '';

    private string $twitterDescription = '';

    private string $twitterImage = '';

    private ?array $schemaOrg = null;

    private ?string $prevUrl = null;

    private ?string $nextUrl = null;

    public function setTitle(string $title, bool $appendSuffix = true): static
    {
        $suffix = setting('seo_title_suffix', ' | '.config('app.name'));
        $this->title = $appendSuffix && $suffix && ! str_ends_with($title, trim($suffix))
            ? $title.$suffix
            : $title;

        return $this;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function setKeywords(string $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function setCanonical(string $url): static
    {
        $this->canonical = $url;

        return $this;
    }

    public function setRobots(string $directive): static
    {
        $this->robots = $directive;

        return $this;
    }

    public function noindex(bool $nofollow = false): static
    {
        $this->robots = $nofollow ? 'noindex,nofollow' : 'noindex,follow';

        return $this;
    }

    public function setOgTitle(string $title): static
    {
        $this->ogTitle = $title;

        return $this;
    }

    public function setOgDescription(string $description): static
    {
        $this->ogDescription = $description;

        return $this;
    }

    public function setOgImage(string $url): static
    {
        $this->ogImage = $url;
        $this->twitterImage = $url;

        return $this;
    }

    public function setOgType(string $type): static
    {
        $this->ogType = $type;

        return $this;
    }

    public function setTwitterCard(string $card): static
    {
        $this->twitterCard = $card;

        return $this;
    }

    public function setPagination(?string $prevUrl, ?string $nextUrl): static
    {
        $this->prevUrl = $prevUrl;
        $this->nextUrl = $nextUrl;

        return $this;
    }

    public function setSchema(array $schema): static
    {
        $this->schemaOrg = $schema;

        return $this;
    }

    public function loadFromModel(Model $model): static
    {
        $meta = method_exists($model, 'seoMeta') ? $model->seoMeta : null;

        $title = $meta?->title ?? $model->meta_title ?? $model->title ?? '';
        $desc = $meta?->description ?? $model->meta_description ?? $model->short ?? $model->description ?? '';
        $image = $meta?->og_image ?? '';

        if ($title) {
            $this->setTitle($title);
        }
        if ($desc) {
            $this->setDescription($desc);
        }
        if ($image) {
            $this->setOgImage($image);
        }

        $this->robots = $meta?->robots ?? 'index,follow';
        $this->canonical = $meta?->canonical_url ?? ($model->url ?? url()->current());
        $this->ogType = $meta?->og_type ?? 'website';
        $this->keywords = $meta?->keywords ?? $model->meta_keywords ?? '';

        if ($meta?->schema_custom) {
            $this->schemaOrg = $meta->schema_custom;
        }

        return $this;
    }

    public function render(): string
    {
        $appName = config('app.name');
        $title = $this->title ?: setting('meta_title', $appName);
        $desc = $this->description ?: setting('meta_description', '');
        $keywords = $this->keywords ?: setting('meta_keywords', '');
        $canonical = $this->canonical ?: url()->current();
        $ogImage = $this->ogImage ?: setting('seo_og_image_default', getMeta());
        $ogTitle = $this->ogTitle ?: $title;
        $ogDesc = $this->ogDescription ?: $desc;
        $twTitle = $this->twitterTitle ?: $title;
        $twDesc = $this->twitterDescription ?: $desc;
        $twImage = $this->twitterImage ?: $ogImage;
        $twSite = setting('seo_twitter_site', '');
        $siteName = setting('seo_site_name', $appName);

        $html = '';
        $html .= '<title>'.e($title).'</title>'."\n";
        $html .= '<meta name="description" content="'.e($desc).'">'."\n";

        if ($keywords) {
            $html .= '<meta name="keywords" content="'.e($keywords).'">'."\n";
        }

        $html .= '<meta name="robots" content="'.e($this->robots).'">'."\n";
        $html .= '<link rel="canonical" href="'.e($canonical).'">'."\n";

        // Open Graph
        $html .= '<meta property="og:type" content="'.e($this->ogType).'">'."\n";
        $html .= '<meta property="og:title" content="'.e($ogTitle).'">'."\n";
        $html .= '<meta property="og:description" content="'.e($ogDesc).'">'."\n";
        $html .= '<meta property="og:url" content="'.e($canonical).'">'."\n";
        $html .= '<meta property="og:site_name" content="'.e($siteName).'">'."\n";

        if ($ogImage) {
            $html .= '<meta property="og:image" content="'.e($ogImage).'">'."\n";
        }

        // Twitter Card
        $html .= '<meta name="twitter:card" content="'.e($this->twitterCard).'">'."\n";
        $html .= '<meta name="twitter:title" content="'.e($twTitle).'">'."\n";
        $html .= '<meta name="twitter:description" content="'.e($twDesc).'">'."\n";

        if ($twSite) {
            $html .= '<meta name="twitter:site" content="'.e($twSite).'">'."\n";
        }
        if ($twImage) {
            $html .= '<meta name="twitter:image" content="'.e($twImage).'">'."\n";
        }

        // Paginación
        if ($this->prevUrl) {
            $html .= '<link rel="prev" href="'.e($this->prevUrl).'">'."\n";
        }
        if ($this->nextUrl) {
            $html .= '<link rel="next" href="'.e($this->nextUrl).'">'."\n";
        }

        // Schema.org JSON-LD
        if ($this->schemaOrg) {
            $html .= '<script type="application/ld+json">'.json_encode($this->schemaOrg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).'</script>'."\n";
        }

        return $html;
    }

    public function reset(): static
    {
        $this->title = '';
        $this->description = '';
        $this->keywords = '';
        $this->canonical = '';
        $this->robots = 'index,follow';
        $this->ogTitle = '';
        $this->ogDescription = '';
        $this->ogImage = '';
        $this->ogType = 'website';
        $this->twitterCard = 'summary_large_image';
        $this->twitterTitle = '';
        $this->twitterDescription = '';
        $this->twitterImage = '';
        $this->schemaOrg = null;
        $this->prevUrl = null;
        $this->nextUrl = null;

        return $this;
    }
}
