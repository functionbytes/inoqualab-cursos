<?php

namespace App\Http\Sitemap;

class SitemapBuilder
{
    private array $items = [];

    private int $maxItems;

    public function __construct()
    {
        $this->maxItems = config('sitemap.max_items', 50000);
    }

    public function add(string $loc, ?string $lastmod = null, string $priority = '0.5', string $changefreq = 'weekly'): static
    {
        if (count($this->items) >= $this->maxItems) {
            return $this;
        }

        $this->items[$loc] = [
            'loc' => $loc,
            'lastmod' => $lastmod,
            'priority' => $priority,
            'changefreq' => $changefreq,
        ];

        return $this;
    }

    public function addModel(string $modelClass): static
    {
        if (! class_exists($modelClass)) {
            return $this;
        }

        $items = $modelClass::getSitemapItems();

        foreach ($items as $model) {
            if (count($this->items) >= $this->maxItems) {
                break;
            }

            $this->add(
                loc: $model->url,
                lastmod: $model->updated_at?->toAtomString(),
                priority: $model->sitemap_priority,
                changefreq: $model->sitemap_changefreq,
            );
        }

        return $this;
    }

    public function getItems(): array
    {
        return array_values($this->items);
    }

    public function render(): string
    {
        return view('sitemap.xml', ['items' => $this->getItems()])->render();
    }

    public function renderIndex(array $sitemaps): string
    {
        return view('sitemap.index', compact('sitemaps'))->render();
    }
}
