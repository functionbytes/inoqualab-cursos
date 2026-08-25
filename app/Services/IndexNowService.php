<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    public function enabled(): bool
    {
        // Apagado mientras no se encienda a propósito: avisa a buscadores externos.
        return settingEnabled('seo_indexnow_enabled')
            && ! empty(setting('seo_indexnow_key'));
    }

    public function key(): string
    {
        return (string) setting('seo_indexnow_key', '');
    }

    public function submit(array|string $urls): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $urls = (array) $urls;
        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $key = $this->key();

        $urls = array_filter($urls, fn ($u) => str_contains($u, $host));

        if (empty($urls)) {
            return false;
        }

        try {
            $response = Http::timeout(10)->post('https://api.indexnow.org/indexnow', [
                'host' => $host,
                'key' => $key,
                'urlList' => array_values($urls),
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('IndexNow submit failed', ['status' => $response->status(), 'body' => $response->body()]);

            return false;
        } catch (\Throwable $e) {
            Log::error('IndexNow error', ['message' => $e->getMessage()]);

            return false;
        }
    }
}
