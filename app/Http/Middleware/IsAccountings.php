<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAccountings
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->role === 'accounting')) {
            return $next($request);
        } else {
            return redirect()->route('validation');
        }
    }
}
