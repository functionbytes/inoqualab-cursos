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
            } elseif ($referer = $request->header('referer')) {
                $request->session()->put('url.intended', $referer);
            }

            return redirect()->route('session.expired');
        }

        return parent::handle($request, $next, ...$guards);
    }
}
