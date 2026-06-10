<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsManager
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->role === 'manager')) {
            return $next($request);
        } else {
            return redirect()->route('validation');
        }
    }
}
