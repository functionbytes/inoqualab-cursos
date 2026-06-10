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
use App\Structure\Elements;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $enterprises = Enterprise::latest()->count();
        $blogs = Blog::latest()->count();
        $users = User::latest()->count();
        $courses = Course::latest()->count();
        $newsletters = Newsletter::latest()->count();
        $contacts = Contact::latest()->take(5)->get();

        $useradmins = User::where('role', 'manager')->count();
        $usercustomers = User::where('role', 'customers')->count();
        $userenterprises = User::where('role', 'enterprises')->count();

        $orders = Order::count();
        $online = Order::where('method_id', 1)->where('condition_id', 4)->count();
        $agreements = Order::where('method_id', 1)->where('condition_id', 4)->count();
        $total = 0;

        $analyticsOrders = Order::where('created_at', '>=', Carbon::now()->startOfYear())->get();

        $analyticsOrders = $analyticsOrders->groupBy(function ($val) {
            return Carbon::parse($val->created_at)->format('M');
        });

        $viewsOrders = new Collection;

        foreach ($analyticsOrders as $order) {
            $element = new Elements;
            $element->number = (int) $order->count();
            $element->date = Carbon::parse($order->first()->created_at)->format('M');
            $viewsOrders->push($element);
        }

        $viewsOrders = $viewsOrders->pluck('number');

        $analyticsEaning = Order::where('created_at', '>=', Carbon::now()->add(-7, 'day')->format('Y-m-d'))->get();

        $analyticsEanings = $analyticsEaning->groupBy(function ($val) {
            return Carbon::parse($val->created_at)->format('d-m');
        });

        $viewsEanings = new Collection;

        foreach ($analyticsEanings as $item) {
            $element = new Elements;
            $element->number = (int) $item->count();
            $element->date = Carbon::parse($item->first()->created_at)->format('d-m');
            $viewsEanings->push($element);
        }

        $monthEanings = $viewsEanings->pluck('date');
        $numberEanings = $viewsEanings->pluck('number');

        return view('supports.views.dashboard.index')->with([
            'viewOrders' => $viewsOrders,
            'analyticsEanings' => $analyticsEanings,
            'analyticsEaning' => $analyticsEaning,
            'numberEanings' => $numberEanings,
            'analyticsOrders' => $analyticsOrders,
            'monthEanings' => $monthEanings,
            'contacts' => $contacts,
            'users' => $users,
            'blogs' => $blogs,
            'courses' => $courses,
            'total' => $total,
            'orders' => $orders,
            'online' => $online,
            'newsletters' => $newsletters,
            'useradmins' => $useradmins,
            'usercustomers' => $usercustomers,
            'userenterprises' => $userenterprises,
            'enterprises' => $enterprises,
            'agreements' => $agreements,
        ]);
    }
}
