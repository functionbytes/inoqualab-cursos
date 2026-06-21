<?php

namespace App\Jobs;

use App\Models\Seo\SeoMeta;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckBrokenLinksJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 3600;

    public int $backoff = 10;

    public function __construct()
    {
        $this->onQueue('seo');
    }

    public function handle(): void
    {
        $progressKey = 'seo.broken_links.progress';
        $resultsKey = 'seo.broken_links.results';

        $metas = SeoMeta::query()
            ->whereNotNull('canonical_url')
            ->where('canonical_url', '!=', '')
            ->select(['id', 'title', 'canonical_url'])
            ->get();

        $total = $metas->count();
        $broken = [];
        $checked = 0;

        Cache::put($progressKey, ['status' => 'running', 'checked' => 0, 'total' => $total], 7200);

        foreach ($metas as $meta) {
            try {
                $response = Http::withoutRedirecting()->timeout(8)->head($meta->canonical_url);
                $status = $response->status();
                if ($status >= 400) {
                    $broken[] = [
                        'meta_id' => $meta->id,
                        'title' => $meta->title ?? '(sin título)',
                        'url' => $meta->canonical_url,
                        'status' => $status,
                    ];
                }
            } catch (\Throwable $e) {
                $broken[] = [
                    'meta_id' => $meta->id,
                    'title' => $meta->title ?? '(sin título)',
                    'url' => $meta->canonical_url,
                    'status' => 0,
                    'error' => $e->getMessage(),
                ];
            }

            $checked++;
            if ($checked % 10 === 0) {
                Cache::put($progressKey, ['status' => 'running', 'checked' => $checked, 'total' => $total], 7200);
            }
        }

        Cache::put($progressKey, ['status' => 'completed', 'checked' => $checked, 'total' => $total], 3600);
        Cache::put($resultsKey, $broken, 86400);

        Log::info("CheckBrokenLinksJob: {$checked} checked, ".count($broken).' broken.');
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CheckBrokenLinksJob failed', ['error' => $exception->getMessage()]);
    }
}
