<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsTxtController extends Controller
{
    public function serve(): Response
    {
        $content = setting('robots_txt') ?: config('seo.robots_txt_default');

        // La línea Sitemap se genera con el dominio real del entorno (no hardcodeada):
        // se elimina cualquier "Sitemap:" del contenido base y se añade la dinámica.
        $lines = preg_split('/\r?\n/', (string) $content);
        $lines = array_filter($lines, fn ($line) => stripos(trim($line), 'sitemap:') !== 0);
        $content = rtrim(implode("\n", $lines))."\n\nSitemap: ".route('sitemap.index');

        return response($content, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
