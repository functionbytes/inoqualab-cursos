<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard()
    {

        $user = app('customer');
        $courses = $user->inscriptions()->with(['course.media', 'certificate'])->get();

        // La variante la elige el manager en Configuración › Portal del alumno.
        $variant = portalVariant('customers_dashboard_variant');

        return view('customers.views.dashboard.index'.$variant, [
            'courses' => $courses,
            'user' => $user,
        ]);

    }
}
