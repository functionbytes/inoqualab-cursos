<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsDistributor
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->role === 'distributor')) {
            $distributor = Auth::user()->relationsDistributor;
            if ($distributor) {
                $request->attributes->set('distributor', $distributor);
                $request->session()->put('distributor', $distributor);
                app()->instance('distributor', $distributor);
            } else {
                // Sin distribuidor asociado: cualquier app('distributor') aguas abajo
                // responde 403 limpio en vez de un 500 BindingResolutionException.
                app()->bind('distributor', fn () => abort(403, 'No tienes un distribuidor asociado.'));
            }

            return $next($request);
        } else {
            return redirect()->route('validation');
        }
    }
}
