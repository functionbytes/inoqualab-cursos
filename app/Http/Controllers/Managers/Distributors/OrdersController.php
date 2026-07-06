<?php

namespace App\Http\Controllers\Managers\Distributors;

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

        // Distributor::orders() apunta a OrderActivity (sin condition/method/type);
        // ordersActititys() es la relación correcta hacia Order (hasManyThrough).
        $orders = Distributor::slack($slack)->ordersActititys()->latest()->with(['condition', 'method', 'type']);
        $methods = OrderMethod::latest()->get()->pluck('title', 'id');
        $conditions = OrderCondition::latest()->get()->pluck('title', 'id');
        $types = OrderType::latest()->get()->pluck('title', 'id');

        if ($searchKey) {
            $orders = $orders->where('slack', 'like', '%'.$searchKey.'%');
        }

        if ($method) {
            $orders = $orders->where('method_id', $method);
        }

        if ($condition) {
            $orders = $orders->where('condition_id', $condition);
        }

        if ($type) {
            $orders = $orders->where('type_id', $type);
        }

        $orders = $orders->paginate(paginationNumber());

        return view('managers.views.distributors.orders.index')->with([
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
