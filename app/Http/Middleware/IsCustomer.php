<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsCustomer
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->role === 'customer')) {
            $customer = Auth::user();
            $request->attributes->set('customer', $customer);
            $request->session()->put('customer', $customer);
            app()->instance('customer', $customer);

            return $next($request);
        } else {
            return redirect()->route('validation');
        }
    }
}
