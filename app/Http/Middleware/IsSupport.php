<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsSupport
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->role === 'support')) {
            $support = Auth::user();
            if ($support) {
                $request->attributes->set('support', $support);
                $request->session()->put('support', $support);
                app()->instance('support', $support);
            }

            return $next($request);
        } else {
            return redirect()->route('validation');
        }
    }
}
