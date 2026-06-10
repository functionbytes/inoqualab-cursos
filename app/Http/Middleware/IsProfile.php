<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsProfile
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            // $auth = Auth::user();
            // if ($auth->setting == 1) {
            return $next($request);
            // }else{
            // return redirect()->route('customers.profiles');
            // }
        } else {
            return redirect('/');
        }

    }
}
