<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Models\Seo\SeoWebVital;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeoWebVitalsController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'metric' => ['required', 'string', 'in:LCP,INP,CLS,FCP,TTFB'],
            'value' => ['required', 'numeric', 'min:0'],
            'url' => ['required', 'string', 'max:500'],
            'device' => ['nullable', 'string', 'in:mobile,desktop,unknown'],
            'connection' => ['nullable', 'string', 'max:16'],
            'navigation_type' => ['nullable', 'string', 'max:32'],
        ]);

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
