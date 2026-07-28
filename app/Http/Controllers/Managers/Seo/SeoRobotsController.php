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

        // La fuente de verdad es el ajuste: /robots.txt lo sirve
        // RobotsTxtController, que además genera la línea Sitemap con el dominio
        // real del entorno. Antes se escribía también public/robots.txt, y ese
        // archivo estático se servía ANTES que la ruta, dejándola inservible y
        // congelando el dominio del sitemap al del momento de guardar.
        updateSettings(['robots_txt' => $request->robots_txt]);

        $this->descartarArchivoEstatico();

        return response()->json([
            'success' => true,
            'message' => 'robots.txt actualizado correctamente.',
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $default = "User-agent: *\nAllow: /\nDisallow: /panel/\nDisallow: /manager/\n\nSitemap: ".url('/sitemap.xml');

        updateSettings(['robots_txt' => $default]);

        $this->descartarArchivoEstatico();

        return response()->json([
            'success' => true,
            'message' => 'robots.txt restablecido al contenido por defecto.',
            'content' => $default,
        ]);
    }

    /**
     * Borra public/robots.txt si quedó de la versión anterior.
     *
     * Mientras ese archivo exista, el servidor web lo sirve como estático y
     * nunca se llega a la ruta dinámica, así que editar desde el panel no
     * tendría ningún efecto visible.
     */
    private function descartarArchivoEstatico(): void
    {
        $ruta = public_path('robots.txt');

        if (File::exists($ruta)) {
            File::delete($ruta);
        }
    }
}
