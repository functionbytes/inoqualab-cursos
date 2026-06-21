<?php

namespace App\Jobs;

use App\Models\Seo\SeoMeta;
use App\Services\SeoAuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BulkSeoAuditJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 3600;

    public int $backoff = 60;

    public function __construct()
    {
        $this->onQueue('seo');
    }

    public function handle(SeoAuditService $auditService): void
    {
        $progressKey = 'seo.bulk_audit.progress';
        $total = SeoMeta::count();
        $processed = 0;

        Cache::put($progressKey, [
            'status' => 'running',
            'processed' => 0,
            'total' => $total,
            'started_at' => now()->toIso8601String(),
        ], 7200);

        try {
            SeoMeta::orderBy('id')->chunk(50, function ($metas) use ($auditService, &$processed, $total, $progressKey) {
                foreach ($metas as $meta) {
                    try {
                        $auditService->auditMeta($meta);
                    } catch (\Throwable $e) {
                        Log::warning("BulkSeoAuditJob: failed to audit meta #{$meta->id}: {$e->getMessage()}");
                    }
                    $processed++;
                }

                Cache::put($progressKey, [
                    'status' => 'running',
                    'processed' => $processed,
                    'total' => $total,
                    'started_at' => Cache::get($progressKey)['started_at'] ?? now()->toIso8601String(),
                ], 7200);
            });

            Cache::put($progressKey, [
                'status' => 'completed',
                'processed' => $processed,
                'total' => $total,
                'completed_at' => now()->toIso8601String(),
            ], 3600);
        } catch (\Throwable $e) {
            Cache::put($progressKey, [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'processed' => $processed,
                'total' => $total,
            ], 3600);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('BulkSeoAuditJob failed', ['error' => $exception->getMessage()]);
        Cache::put('seo.bulk_audit.progress', ['status' => 'failed', 'error' => $exception->getMessage()], 3600);
    }
}
