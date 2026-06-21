<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsEnterprise
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->role === 'enterprise')) {
            $enterprise = Auth::user()->relationsEnterprises;
            if ($enterprise) {
                $request->attributes->set('enterprise', $enterprise);
                $request->session()->put('enterprise', $enterprise);
                app()->instance('enterprise', $enterprise);
            } else {
                // Sin empresa asociada: cualquier app('enterprise') aguas abajo
                // responde 403 limpio en vez de un 500 BindingResolutionException.
                app()->bind('enterprise', fn () => abort(403, 'No tienes una empresa asociada.'));
            }

            return $next($request);
        } else {
            return redirect()->route('validation');
        }

    }
}
