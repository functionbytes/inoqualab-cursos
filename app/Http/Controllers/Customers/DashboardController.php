<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard()
    {

        $user = app('customer');
        $courses = $user->inscriptions()->with(['course.media', 'certificate'])->get();

        return view('customers.views.dashboard.index', [
            'courses' => $courses,
            'user' => $user,
        ]);

    }
}
