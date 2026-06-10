<?php

namespace App\Http\Middleware;

use Closure;

class IsVerified
{
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        // if(! is_null($request->user()) && !$request->user()->hasVerifiedEmail()) {
        //  return redirect()->route('verification.notice');
        // }

        return $next($request);
    }
}
