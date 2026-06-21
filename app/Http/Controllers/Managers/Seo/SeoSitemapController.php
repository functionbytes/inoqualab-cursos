<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SeoSitemapController extends Controller
{
    public function index(): View
    {
        $sitemapTypes = [
            'index' => 'sitemap.index',
            'main' => 'sitemap.main',
            'courses' => 'sitemap.courses',
            'blogs' => 'sitemap.blogs',
        ];

        $sitemaps = [];

        foreach ($sitemapTypes as $type => $routeName) {
            $url = null;

            if (\Route::has($routeName)) {
                $url = route($routeName);
            }

            $sitemaps[] = [
                'name' => $type,
                'url' => $url,
                'has_cache' => Cache::has('sitemap.'.$type),
            ];
        }

        $totalSitemaps = count($sitemaps);
        $cachedCount = collect($sitemaps)->where('has_cache', true)->count();

        return view('managers.views.seo.sitemap.index', compact('sitemaps', 'totalSitemaps', 'cachedCount'));
    }

    public function clearCache(Request $request): JsonResponse
    {
        Cache::forget('sitemap.main');
        Cache::forget('sitemap.courses');
        Cache::forget('sitemap.blogs');
        Cache::forget('sitemap.index');

        return response()->json([
            'success' => true,
            'message' => 'Caché de sitemaps limpiado correctamente.',
        ]);
    }

    public function generate(Request $request): JsonResponse
    {
        Cache::forget('sitemap.main');
        Cache::forget('sitemap.courses');
        Cache::forget('sitemap.blogs');
        Cache::forget('sitemap.index');

        return response()->json([
            'success' => true,
            'message' => 'Caché limpiado. Los sitemaps se regenerarán en la próxima visita.',
        ]);
    }
}
