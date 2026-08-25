<?php

namespace App\Http\Middleware;

use App\Models\Seo\SeoRedirect;
use App\Models\Seo\SeoRedirectHit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleSeoRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = static::resolve($request);

        return $response ?? $next($request);
    }

    /**
     * Resuelve el redirect para la request, si hay uno activo que aplique.
     *
     * Se llama desde DOS sitios que cubren casos distintos:
     * - handle() arriba: el caso en que el path a redirigir COINCIDE con una
     *   ruta real ya registrada (raro, pero posible).
     * - El hook de NotFoundHttpException en bootstrap/app.php: el caso real
     *   y mayoritario de un redirect -- una URL vieja que YA NO tiene
     *   ningún controller detrás. Ese path no matchea ninguna ruta, así que
     *   el router lanza NotFoundHttpException ANTES de que corra ningún
     *   middleware del grupo 'web' (incluido este, que estaba registrado
     *   ahí vía $middleware->web(prepend:...)) -- por eso ningún redirect
     *   a una URL genuinamente inexistente funcionaba pese a que el CRUD de
     *   redirects, el modelo, la cache y el desglose de hits ya estaban
     *   completos y probados.
     */
    public static function resolve(Request $request): ?Response
    {
        if (! $request->isMethodSafe()) {
            return null;
        }

        $path = '/'.ltrim($request->getPathInfo(), '/');

        if (static::shouldSkip($path)) {
            return null;
        }

        $redirect = static::findRedirect($path);

        return $redirect ? static::respondWithRedirect($redirect) : null;
    }

    private static function respondWithRedirect(SeoRedirect $redirect): Response
    {
        // Contador total (para el listado) + desglose diario (para el
        // gráfico de analytics()/seo_redirect_hits, que hasta ahora nadie
        // alimentaba pese a que SeoRedirectController::analytics() ya lo
        // leía -- el gráfico siempre mostraba la línea en cero).
        $redirect->increment('hits_count');
        SeoRedirectHit::recordHit($redirect->id);

        $target = $redirect->target_path;

        // Si es ruta relativa, convertir a URL completa
        if (! str_starts_with($target, 'http')) {
            $target = url($target);
        }

        return redirect($target, $redirect->status_code);
    }

    private static function shouldSkip(string $path): bool
    {
        return str_starts_with($path, '/panel/')
            || str_starts_with($path, '/manager/')
            || str_starts_with($path, '/api/')
            || str_starts_with($path, '/_')
            || preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|map)$/i', $path);
    }

    private static function findRedirect(string $path): ?SeoRedirect
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
