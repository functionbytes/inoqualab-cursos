<?php

namespace App\Http\Controllers\Distributors\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function dashboard(): View
    {
        $distributor = app('distributor');

        $enterpriseIds = $distributor->enterprises()->pluck('enterprises.id');

        $usersCount = DB::table('enterprise_user')
            ->whereIn('enterprise_id', $enterpriseIds)
            ->distinct('user_id')
            ->count('user_id');

        $recentOrders = $distributor->ordersActititys()
            ->descending()
            ->with(['user', 'activity.enterprise'])
            ->take(5)
            ->get();

        return view('distributors.views.dashboard.index')->with([
            'enterprisesCount' => $enterpriseIds->count(),
            'usersCount' => $usersCount,
            'ordersCount' => $distributor->ordersActititys()->count(),
            'invoicesCount' => $distributor->invoices()->count(),
            'recentOrders' => $recentOrders,
        ]);
    }
}
