<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\Settings\UpdateSeoSettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SeoSettingsController extends Controller
{
    public function index(): View
    {
        $settings = [
            'seo_title_suffix' => setting('seo_title_suffix', ''),
            'seo_site_name' => setting('seo_site_name', ''),
            'seo_og_image_default' => setting('seo_og_image_default', ''),
            'seo_twitter_site' => setting('seo_twitter_site', ''),
            'seo_google_verification' => setting('seo_google_verification', ''),
            'seo_bing_verification' => setting('seo_bing_verification', ''),
            'seo_pinterest_verification' => setting('seo_pinterest_verification', ''),
            'seo_baidu_verification' => setting('seo_baidu_verification', ''),
            'seo_yandex_verification' => setting('seo_yandex_verification', ''),
            'seo_indexnow_enabled' => setting('seo_indexnow_enabled', '0'),
            'seo_indexnow_key' => setting('seo_indexnow_key', ''),
            'robots_txt' => setting('robots_txt', config('seo.robots_txt_default', '')),
            'llms_txt' => setting('llms_txt', ''),
        ];

        return view('managers.views.settings.seo.index', compact('settings'));
    }

    public function update(UpdateSeoSettingsRequest $request): JsonResponse
    {
        updateSettings($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Configuración SEO guardada correctamente.',
        ]);
    }

    public function updateRobots(Request $request): JsonResponse
    {
        $request->validate([
            'robots_txt' => ['required', 'string'],
        ]);

        $content = $request->input('robots_txt');

        updateSettings(['robots_txt' => $content]);

        // Nginx sirve /robots.txt como fichero estático — escribir en public/ para que lo encuentre
        File::put(public_path('robots.txt'), $content);

        return response()->json([
            'success' => true,
            'message' => 'Archivo robots.txt guardado correctamente.',
        ]);
    }

    public function updateLlms(Request $request): JsonResponse
    {
        $request->validate([
            'llms_txt' => ['required', 'string'],
        ]);

        updateSettings(['llms_txt' => $request->input('llms_txt')]);

        return response()->json([
            'success' => true,
            'message' => 'Archivo llms.txt guardado correctamente.',
        ]);
    }
}
