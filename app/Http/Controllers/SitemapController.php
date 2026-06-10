<?php

namespace App\Http\Controllers;

use App\Http\Sitemap\SitemapBuilder;
use App\Models\Blog\Blog;
use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    private const TTL = 86400; // 24 horas

    public function index(): Response
    {
        $xml = Cache::remember('sitemap.index', self::TTL, function () {
            $sitemaps = [
                ['loc' => route('sitemap.main'),   'lastmod' => now()->toAtomString()],
                ['loc' => route('sitemap.courses'), 'lastmod' => now()->toAtomString()],
                ['loc' => route('sitemap.blogs'),   'lastmod' => now()->toAtomString()],
            ];

            return (new SitemapBuilder)->renderIndex($sitemaps);
        });

        return $this->xmlResponse($xml);
    }

    public function main(): Response
    {
        $xml = Cache::remember('sitemap.main', self::TTL, function () {
            $builder = new SitemapBuilder;

            foreach (config('sitemap.static_urls', []) as $entry) {
                $builder->add(
                    loc: url($entry['loc']),
                    priority: $entry['priority'] ?? '0.6',
                    changefreq: $entry['changefreq'] ?? 'monthly',
                );
            }

            $builder->addModel(Bundle::class);

            return $builder->render();
        });

        return $this->xmlResponse($xml);
    }

    public function courses(): Response
    {
        $xml = Cache::remember('sitemap.courses', self::TTL, function () {
            return (new SitemapBuilder)->addModel(Course::class)->render();
        });

        return $this->xmlResponse($xml);
    }

    public function blogs(): Response
    {
        $xml = Cache::remember('sitemap.blogs', self::TTL, function () {
            return (new SitemapBuilder)->addModel(Blog::class)->render();
        });

        return $this->xmlResponse($xml);
    }

    private function xmlResponse(string $xml): Response
    {
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
