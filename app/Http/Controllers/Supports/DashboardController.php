<?php

namespace App\Http\Controllers\Supports;

use App\Http\Controllers\Controller;
use App\Models\Blog\Blog;
use App\Models\Contact;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Newsletter;
use App\Models\Order\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function dashboard(): View
    {
        $monthlyOrders = Order::query()
            ->selectRaw('MONTH(created_at) as month_num, COUNT(*) as total')
            ->where('created_at', '>=', Carbon::now()->startOfYear())
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->pluck('total', 'month_num');

        $viewOrders = collect(range(1, 12))
            ->map(fn ($m) => (int) ($monthlyOrders->get($m, 0)));

        $weeklyOrders = Order::query()
            ->selectRaw('DATE_FORMAT(created_at, "%d-%m") as date, COUNT(*) as total')
            ->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay())
            ->groupByRaw('DATE_FORMAT(created_at, "%d-%m")')
            ->orderByRaw('MIN(created_at)')
            ->get();

        return view('supports.views.dashboard.index', [
            'viewOrders' => $viewOrders->values(),
            'monthEanings' => $weeklyOrders->pluck('date'),
            'numberEanings' => $weeklyOrders->pluck('total'),
            'contacts' => Contact::latest()->take(5)->get(),
            'users' => User::count(),
            'blogs' => Blog::count(),
            'courses' => Course::count(),
            'total' => 0,
            'orders' => Order::count(),
            'online' => Order::where('method_id', 1)->where('condition_id', 4)->count(),
            'newsletters' => Newsletter::count(),
            'useradmins' => User::where('role', 'manager')->count(),
            'usercustomers' => User::where('role', 'customers')->count(),
            'userenterprises' => User::where('role', 'enterprises')->count(),
            'enterprises' => Enterprise::count(),
            'agreements' => Order::where('method_id', 1)->where('condition_id', 4)->count(),
        ]);
    }
}
