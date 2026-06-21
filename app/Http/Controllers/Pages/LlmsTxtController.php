<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class LlmsTxtController extends Controller
{
    public function serve(): Response
    {
        if (! config('seo.llms_txt_enabled')) {
            abort(404);
        }

        $customContent = setting('llms_txt', '');

        if ($customContent) {
            return response($customContent, 200)
                ->header('Content-Type', 'text/plain')
                ->header('Cache-Control', 'public, max-age=3600');
        }

        $content = $this->generateDefaultContent();

        return response($content, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    private function generateDefaultContent(): string
    {
        $siteName = setting('seo_site_name', config('app.name'));
        $description = setting('seo_meta_description', '');
        $url = config('app.url');

        $lines = [
            "# {$siteName}",
            '',
        ];

        if ($description) {
            $lines[] = "> {$description}";
            $lines[] = '';
        }

        $lines[] = "URL: {$url}";
        $lines[] = '';
        $lines[] = '## Secciones disponibles';
        $lines[] = '';
        $lines[] = "- Cursos: {$url}/cursos";
        $lines[] = "- Blog: {$url}/blog";
        $lines[] = "- Contacto: {$url}/contacto";

        return implode("\n", $lines);
    }
}
