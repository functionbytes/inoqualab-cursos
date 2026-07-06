<?php

namespace App\Http\Controllers\Accountings\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request, $slack)
    {

        $searchKey = $request->search;
        $condition = $request->condition;
        $type = $request->type;
        $method = $request->methods;

        // orders_activity no tiene method_id/condition_id/type_id (viven en la orden
        // relacionada); se filtra vía whereHas sobre order y se precarga anidado.
        $orders = Distributor::slack($slack)->ordersActitity()->with(['order.user', 'order.condition', 'order.method', 'order.type']);
        $methods = OrderMethod::latest()->get();
        $conditions = OrderCondition::latest()->get();
        $types = OrderType::latest()->get();

        if ($searchKey) {
            $orders = $orders->whereHas('order', function ($query) use ($searchKey) {
                $query->where('slack', 'like', '%'.$searchKey.'%');
            });
        }

        if ($method) {
            $orders = $orders->whereHas('order', function ($query) use ($method) {
                $query->where('method_id', $method);
            });
        }

        if ($condition) {
            $orders = $orders->whereHas('order', function ($query) use ($condition) {
                $query->where('condition_id', $condition);
            });
        }

        if ($type) {
            $orders = $orders->whereHas('order', function ($query) use ($type) {
                $query->where('type_id', $type);
            });
        }

        $orders = $orders->paginate(paginationNumber());

        return view('accountings.views.distributors.orders.index')->with([
            'orders' => $orders,
            'conditions' => $conditions,
            'condition' => $condition,
            'types' => $types,
            'type' => $type,
            'methods' => $methods,
            'method' => $method,
            'searchKey' => $searchKey,
        ]);

    }
}
