<?php

namespace App\Http\Controllers\Supports\Distributors\Orders;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    public function index(Request $request, $slack)
    {
        $searchKey = $request->search ?? null;
        $condition = $request->condition ?? null;
        $type = $request->type ?? null;
        $method = $request->methods ?? null;

        $distributor = Distributor::slack($slack);
        $orders = $distributor->ordersActititys()?->descending()
            ->with(['user', 'activity.enterprise']);

        $methods = OrderMethod::latest()->get();
        $conditions = OrderCondition::latest()->get();
        $types = OrderType::latest()->get();

        if ($searchKey) {
            $orders = $orders->where(function ($query) use ($searchKey) {
                $query->where('orders.slack', 'like', '%'.$searchKey.'%')
                    ->orWhereHas('user', function ($query) use ($searchKey) {
                        $query->where('firstname', 'like', '%'.$searchKey.'%')
                            ->orWhere('lastname', 'like', '%'.$searchKey.'%')
                            ->orWhere(DB::raw("CONCAT(firstname, ' ', lastname)"), 'like', '%'.$searchKey.'%')
                            ->orWhere('email', 'like', '%'.$searchKey.'%')
                            ->orWhere('identification', 'like', '%'.$searchKey.'%');
                    });
            });

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

        return view('supports.views.distributors.orders.orders.index')->with([
            'orders' => $orders,
            'conditions' => $conditions,
            'condition' => $condition,
            'types' => $types,
            'type' => $type,
            'methods' => $methods,
            'method' => $method,
            'searchKey' => $searchKey,
            'distributor' => $distributor,
        ]);
    }

    public function view($slack)
    {

        $order = Order::slack($slack);

        // El cliente puede haberse borrado (soft delete) después de la orden;
        // la vista lee $order->user->firstname sin null-check.
        abort_unless($order->user instanceof User, 404, 'El cliente de esta orden ya no existe.');

        return view('supports.views.distributors.orders.orders.view')->with([
            'order' => $order,
        ]);

    }
}
