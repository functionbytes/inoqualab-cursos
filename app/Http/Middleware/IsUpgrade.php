<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsUpgrade
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $auth = Auth::user();
            $validation = $auth->validation;

            // if ($validation == 1) {
            return $next($request);
            // } else {
            //   return redirect()->route('upgrade');
            // }
        } else {
            return redirect('/');
        }

    }
}
