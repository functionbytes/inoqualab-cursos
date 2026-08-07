<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InternalLinkAnalyzer
{
    public function findOrphansByLinks(Collection $urls, int $limit = 20): array
    {
        $inboundLinks = [];
        $scanned = 0;
        $scannedUrls = $urls->take(30);

        foreach ($scannedUrls as $url) {
            try {
                $response = Http::timeout(8)->get($url);
                if ($response->ok()) {
                    preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $response->body(), $matches);
                    foreach ($matches[1] as $href) {
                        $normalized = $this->normalizeUrl($href, $url);
                        if ($normalized !== null) {
                            $inboundLinks[$normalized] = ($inboundLinks[$normalized] ?? 0) + 1;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('InternalLinkAnalyzer: URL inaccesible al escanear enlaces', [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
            }
            $scanned++;
        }

        // Los enlaces entrantes solo se conocen para páginas ENLAZADAS DESDE
        // las primeras 30 (únicas escaneadas como fuente): calcular
        // "huérfanas" contra $urls completo marcaba sistemáticamente como
        // huérfana cualquier URL fuera de esas 30, tuviera o no enlaces
        // entrantes reales desde páginas nunca escaneadas -- en un sitio de
        // 200 URLs, ~170 salían "huérfanas" solo por no haberse revisado.
        // Se limita el chequeo al mismo subconjunto realmente escaneado.
        $orphans = $scannedUrls->filter(fn ($url) => ! isset($inboundLinks[$url]))->values();

        return [
            'orphans' => $orphans->take($limit)->toArray(),
            'scanned' => $scanned,
            'total_urls' => $urls->count(),
            'inbound_counts' => collect($inboundLinks)->sortDesc()->take(10)->toArray(),
        ];
    }

    private function normalizeUrl(string $href, string $baseUrl): ?string
    {
        if (str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
            return null;
        }

        if (str_starts_with($href, '/')) {
            $parsed = parse_url($baseUrl);

            return ($parsed['scheme'] ?? 'https').'://'.($parsed['host'] ?? '').$href;
        }

        if (str_starts_with($href, 'http')) {
            $baseHost = parse_url($baseUrl, PHP_URL_HOST);
            $hrefHost = parse_url($href, PHP_URL_HOST);

            return $baseHost === $hrefHost ? $href : null;
        }

        return null;
    }
}
