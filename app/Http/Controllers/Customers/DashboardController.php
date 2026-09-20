<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard()
    {

        $user = app('customer');
        // course.categorie: index.blade.php/index-b.blade.php lo usan para la
        // "ruta formativa" -- sin precargarlo es una query extra por curso (N+1).
        $courses = $user->inscriptions()->with(['course.media', 'course.categorie', 'certificate'])->get();

        // La variante la elige el manager en Configuración › Portal del alumno.
        $variant = portalVariant('customers_dashboard_variant');

        return view('customers.views.dashboard.index'.$variant, [
            'courses' => $courses,
            'user' => $user,
        ]);

    }
}
