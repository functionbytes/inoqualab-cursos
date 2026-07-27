<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Content-Security-Policy acotada a lo que este proyecto sostiene hoy.
     *
     * NO se declaran `script-src` ni `style-src` a propósito: hay 159 vistas con
     * <script> inline, 141 con style="" y 22 con handlers on*, así que la única
     * política que no rompería el sitio sería `'unsafe-inline'`, que no aporta
     * protección real contra XSS. Declararla daría una falsa sensación de
     * cobertura; la defensa contra XSS aquí es el purificador (`clean()`).
     *
     * Las directivas que sí se declaran cierran vectores concretos sin depender
     * de refactorizar todo ese inline:
     *
     * - object-src 'none'  → nada usa <object>/<embed>; corta plugins legacy.
     * - base-uri 'self'    → un <base> inyectado no puede redirigir las rutas
     *                        relativas de la página hacia otro dominio.
     * - form-action        → un formulario inyectado no puede exfiltrar datos a
     *                        un tercero. Se permite checkout.wompi.co porque el
     *                        widget de la pasarela publica ahí su propio form.
     * - frame-ancestors    → equivalente moderno de X-Frame-Options, que se
     *                        mantiene arriba para navegadores antiguos.
     *
     * Si algún día se añade `script-src`, hacen falta también:
     * googletagmanager.com, clarity.ms, snap.licdn.com, facebook.net y
     * checkout.wompi.co.
     */
    private const CSP = [
        // Sin `default-src` a propósito: actúa de fallback para script-src, así
        // que declararlo sin 'unsafe-inline' bloquearía los <script> inline de
        // todo el panel. Solo se listan directivas que no tienen ese efecto.
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self' https://checkout.wompi.co",
        "frame-ancestors 'self'",
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', implode('; ', self::CSP));

        return $response;
    }
}
