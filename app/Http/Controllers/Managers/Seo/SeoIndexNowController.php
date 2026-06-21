<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use App\Services\IndexNowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoIndexNowController extends Controller
{
    public function index(): View
    {
        $enabled = setting('seo_indexnow_enabled', '0');
        $key = setting('seo_indexnow_key', '');
        $keyUrl = $key ? url("/{$key}.txt") : null;
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        return view('managers.views.seo.indexnow.index', compact(
            'enabled',
            'key',
            'keyUrl',
            'host',
        ));
    }

    public function submit(Request $request): JsonResponse
    {
        $service = new IndexNowService;

        if (! $service->enabled()) {
            return response()->json([
                'success' => false,
                'message' => 'IndexNow no está habilitado.',
            ], 422);
        }

        $request->validate([
            'urls' => ['required', 'array', 'min:1', 'max:50'],
            'urls.*' => ['url'],
        ]);

        $service->submit($request->urls);

        $count = count($request->urls);

        return response()->json([
            'success' => true,
            'message' => "Se enviaron {$count} URL(s) a IndexNow.",
        ]);
    }
}
