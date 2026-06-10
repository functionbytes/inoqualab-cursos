<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckSession
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Sin usuario → dejar que la página de sesión expirada lo maneje
        if (! $user) {
            return redirect()->route('session.expired');
        }

        // Cuenta deshabilitada → cerrar sesión
        if (! $user->available) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('session.expired');
        }

        // Single-session ("el último login gana"):
        // al hacer login se guarda el ID de sesión nuevo en $user->session.
        // Si MI sesión actual ya no coincide, otro dispositivo me desplazó → expulsar.
        if ($user->session && $user->session !== Session::getId()) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('session.expired', ['reason' => 'device']);
        }

        return $next($request);
    }
}
