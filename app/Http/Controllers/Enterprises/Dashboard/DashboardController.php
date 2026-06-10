<?php

namespace App\Http\Controllers\Enterprises\Dashboard;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard()
    {

        $enterprise = app('enterprise');
        $users = $enterprise->users()->latest()->take(10)->get();

        return view('enterprises.views.dashboard.index')->with([
            'enterprises' => $enterprise,
            'users' => $users,
        ]);

    }
}
