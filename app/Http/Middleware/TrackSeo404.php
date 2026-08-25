<?php

namespace App\Http\Middleware;

use App\Models\Seo\Seo404Log;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackSeo404
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 404) {
            static::track($request);
        }

        return $response;
    }

    /**
     * Registra el hit de un 404 (y, si es la primera vez que se ve esta ruta,
     * dispara la alerta seo_alerts vía Seo404Log::recordHit()).
     *
     * Se llama desde DOS sitios que NO se solapan:
     * - handle() arriba: solo cubre el caso raro de un controller que
     *   devuelve response(..., 404) SIN lanzar una excepción.
     * - El hook de NotFoundHttpException en bootstrap/app.php: cubre el caso
     *   real y mayoritario -- tanto una ruta que no existe (el router lanza
     *   NotFoundHttpException antes de que corra ningún middleware de grupo,
     *   incluido este) como un abort(404) dentro de un controller ya
     *   enrutado (abort() también lanza NotFoundHttpException). En ambos
     *   casos la excepción deshace la pila de middleware SIN ejecutar el
     *   "después" de $next() -- por eso handle() nunca llegaba a este código
     *   para ningún 404 real, pese a que la tabla seo_404_logs existía.
     */
    public static function track(Request $request): void
    {
        if (! $request->isMethodSafe()) {
            return;
        }

        $path = '/'.ltrim($request->getPathInfo(), '/');

        if (static::shouldSkip($path)) {
            return;
        }

        Seo404Log::recordHit(
            path: $path,
            referer: $request->header('referer'),
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );
    }

    private static function shouldSkip(string $path): bool
    {
        return str_starts_with($path, '/panel/')
            || str_starts_with($path, '/api/')
            || preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|map|txt|xml)$/i', $path);
    }
}
