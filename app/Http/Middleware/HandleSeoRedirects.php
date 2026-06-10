<?php

namespace App\Http\Middleware;

use App\Models\Seo\SeoRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleSeoRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        // Solo en peticiones GET/HEAD, ignorar panel y api
        if (! $request->isMethodSafe()) {
            return $next($request);
        }

        $path = '/'.ltrim($request->getPathInfo(), '/');

        if ($this->shouldSkip($path)) {
            return $next($request);
        }

        if ($redirect = $this->findRedirect($path)) {
            // Registrar hit en background
            $redirect->increment('hits_count');

            $target = $redirect->target_path;

            // Si es ruta relativa, convertir a URL completa
            if (! str_starts_with($target, 'http')) {
                $target = url($target);
            }

            return redirect($target, $redirect->status_code);
        }

        return $next($request);
    }

    private function shouldSkip(string $path): bool
    {
        return str_starts_with($path, '/panel/')
            || str_starts_with($path, '/manager/')
            || str_starts_with($path, '/api/')
            || str_starts_with($path, '/_')
            || preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|map)$/i', $path);
    }

    private function findRedirect(string $path): ?SeoRedirect
    {
        $all = SeoRedirect::cachedAll();

        // 1. Exact match (más rápido)
        $exact = $all->first(fn ($r) => ! $r->is_regex && ! $r->is_wildcard && $r->source_path === $path);
        if ($exact) {
            return $exact;
        }

        // 2. Wildcard: /old-path/* → /new-path
        foreach ($all->where('is_wildcard', true) as $r) {
            $pattern = rtrim($r->source_path, '/*');
            if (str_starts_with($path, $pattern.'/') || $path === $pattern) {
                return $r;
            }
        }

        // 3. Regex
        foreach ($all->where('is_regex', true) as $r) {
            if (@preg_match($r->source_path, $path)) {
                return $r;
            }
        }

        return null;
    }
}
