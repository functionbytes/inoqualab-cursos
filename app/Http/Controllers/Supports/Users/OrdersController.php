<?php

namespace App\Http\Controllers\Supports\Users;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request, $slack)
    {

        $user = User::slack($slack);

        $orders = $user->orders;

        return view('supports.views.users.users.orders')->with([
            'orders' => $orders,
        ]);

    }

    public function destroy($slack)
    {
        $order = Order::slack($slack);
        $user = $order->user->slack;
        $order->delete();

        return redirect()->route('manager.users.orders', $user);

    }

    public function view($slack)
    {

        $order = Order::slack($slack);

        return view('supports.views.distributors.orders.orders.view')->with([
            'order' => $order,
        ]);

    }

    public function print($slack)
    {

        $order = Order::slack($slack);

        return view('supports.views.distributors.orders.orders.print')->with([
            'order' => $order,
        ]);

    }
}
