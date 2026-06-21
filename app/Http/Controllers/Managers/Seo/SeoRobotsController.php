<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SeoRobotsController extends Controller
{
    public function index(): View
    {
        $content = setting('robots_txt', '');

        $lines = explode("\n", $content);

        $stats = [
            'user_agents' => 0,
            'allow' => 0,
            'disallow' => 0,
            'sitemaps' => 0,
        ];

        foreach ($lines as $line) {
            $line = trim($line);

            if (stripos($line, 'User-agent:') === 0) {
                $stats['user_agents']++;
            } elseif (stripos($line, 'Allow:') === 0) {
                $stats['allow']++;
            } elseif (stripos($line, 'Disallow:') === 0) {
                $stats['disallow']++;
            } elseif (stripos($line, 'Sitemap:') === 0) {
                $stats['sitemaps']++;
            }
        }

        $public_url = url('/robots.txt');

        return view('managers.views.seo.robots.index', compact('content', 'stats', 'public_url'));
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'robots_txt' => ['required', 'string'],
        ]);

        // Escribir el archivo primero (operación que puede fallar); solo si
        // tiene éxito se persiste en settings, para no desincronizar BD y disco.
        try {
            File::put(public_path('robots.txt'), $request->robots_txt);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo escribir el archivo robots.txt. Verifica los permisos del directorio public.',
            ], 500);
        }

        updateSettings(['robots_txt' => $request->robots_txt]);

        return response()->json([
            'success' => true,
            'message' => 'robots.txt actualizado correctamente.',
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $default = "User-agent: *\nAllow: /\nDisallow: /panel/\nDisallow: /manager/\n\nSitemap: ".url('/sitemap.xml');

        try {
            File::put(public_path('robots.txt'), $default);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo escribir el archivo robots.txt. Verifica los permisos del directorio public.',
            ], 500);
        }

        updateSettings(['robots_txt' => $default]);

        return response()->json([
            'success' => true,
            'message' => 'robots.txt restablecido al contenido por defecto.',
            'content' => $default,
        ]);
    }
}
