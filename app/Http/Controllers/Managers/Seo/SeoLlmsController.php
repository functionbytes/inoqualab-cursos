<?php

namespace App\Http\Controllers\Managers\Seo;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoLlmsController extends Controller
{
    public function index(): View
    {
        $content = setting('llms_txt', '');
        $public_url = url('/llms.txt');

        return view('managers.views.seo.llms.index', compact('content', 'public_url'));
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'llms_txt' => ['required', 'string'],
        ]);

        updateSettings(['llms_txt' => $request->llms_txt]);

        return response()->json(['success' => true]);
    }

    public function reset(Request $request): JsonResponse
    {
        $default = '# '.config('app.name')."\n\n> Plataforma de formación en línea.\n\nURL: ".config('app.url')."\n\n## Recursos\n\n- [Cursos](".config('app.url')."/courses)\n- [Sitemap XML](".config('app.url').'/sitemap.xml)';

        updateSettings(['llms_txt' => $default]);

        return response()->json(['success' => true, 'content' => $default]);
    }
}
