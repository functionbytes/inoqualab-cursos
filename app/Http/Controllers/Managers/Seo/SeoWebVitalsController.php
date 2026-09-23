<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeoWebVitalRequest;
use App\Models\Seo\SeoWebVital;
use Illuminate\Http\JsonResponse;

class SeoWebVitalsController extends Controller
{
    public function store(StoreSeoWebVitalRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $url = $validated['url'];
        $urlPath = parse_url($url, PHP_URL_PATH) ?: '/';
        $metric = strtoupper($validated['metric']);
        $value = (float) $validated['value'];
        $device = $validated['device'] ?? $this->detectDevice($request->userAgent());

        SeoWebVital::create([
            'url' => $url,
            'url_path' => $urlPath,
            'metric' => $metric,
            'value' => $value,
            'rating' => SeoWebVital::rate($metric, $value),
            'device' => $device,
            'connection' => $validated['connection'] ?? null,
            'navigation_type' => $validated['navigation_type'] ?? null,
            'captured_at' => now(),
        ]);

        return response()->json(['ok' => true], 201);
    }

    private function detectDevice(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'unknown';
        }

        return preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $userAgent) === 1 ? 'mobile' : 'desktop';
    }
}
