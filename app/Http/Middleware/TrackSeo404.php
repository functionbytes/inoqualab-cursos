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

        if ($response->getStatusCode() === 404 && $request->isMethodSafe()) {
            $path = '/'.ltrim($request->getPathInfo(), '/');

            if (! $this->shouldSkip($path)) {
                Seo404Log::recordHit(
                    path: $path,
                    referer: $request->header('referer'),
                    ip: $request->ip(),
                    userAgent: $request->userAgent(),
                );
            }
        }

        return $response;
    }

    private function shouldSkip(string $path): bool
    {
        return str_starts_with($path, '/panel/')
            || str_starts_with($path, '/api/')
            || preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|map|txt|xml)$/i', $path);
    }
}
