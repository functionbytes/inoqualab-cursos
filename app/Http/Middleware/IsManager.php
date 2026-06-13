<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsManager
{
    /** Roles (columna `role`) con acceso al panel manager. */
    private const PANEL_ROLES = ['superadmin', 'manager'];

    public function handle($request, Closure $next)
    {
        if (Auth::check() && in_array(Auth::user()->role, self::PANEL_ROLES, true)) {
            return $next($request);
        }

        return redirect()->route('validation');
    }
}
