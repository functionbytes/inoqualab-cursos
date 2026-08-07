<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        if (Auth::guest()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // Solo guardar GET urls como destino post-login — las POST no se pueden replay
            if ($request->isMethod('GET')) {
                $request->session()->put('url.intended', $request->url());
            } elseif (($referer = $request->header('referer')) && $this->isSameHost($referer, $request)) {
                // El Referer lo controla el cliente: sin validar que sea del mismo
                // host, redirect()->intended() (llamado tal cual tras el login,
                // ver LoginController) reenviaría a una URL externa arbitraria —
                // open redirect post-login vía un POST/form cross-site que dispara
                // este middleware.
                $request->session()->put('url.intended', $referer);
            }

            return redirect()->route('session.expired');
        }

        return parent::handle($request, $next, ...$guards);
    }

    /** Compara el host real del Referer contra el de la request (no un prefijo de string). */
    private function isSameHost(string $referer, $request): bool
    {
        return parse_url($referer, PHP_URL_HOST) === $request->getHost();
    }
}
