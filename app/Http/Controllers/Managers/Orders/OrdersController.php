<?php

namespace App\Http\Controllers\Managers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Managers\UpdateOrderRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $searchKey = $request->search;
        $condition = $request->condition;
        $type = $request->type;
        $method = $request->methods;

        // Eager loading para evitar N+1 al listar (la vista usa user/condition/method/type).
        $orders = Order::descending()->with(['user', 'condition', 'method', 'type']);
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

        return view('managers.views.orders.orders.index')->with([
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

    public function view($slack)
    {

        $order = Order::slack($slack);

        $this->authorize('view', $order);

        return view('managers.views.orders.orders.view')->with([
            'order' => $order,
        ]);
    }

    public function edit($slack)
    {

        $order = Order::slack($slack);

        $this->authorize('update', $order);

        $methods = OrderMethod::latest()->get();
        $methods = $methods->pluck('title', 'id');

        $conditions = OrderCondition::latest()->get();
        $conditions = $conditions->pluck('title', 'id');

        $types = OrderType::latest()->get();
        $types = $types->pluck('title', 'id');

        return view('managers.views.orders.orders.edit')->with([
            'order' => $order,
            'conditions' => $conditions,
            'methods' => $methods,
            'types' => $types,
        ]);
    }

    public function update(UpdateOrderRequest $request)
    {
        $order = Order::slack($request->slack);

        // La condicion 4 ("Pagada") es la unica que conserva payment_at; cualquier
        // otra condicion debe limpiarla para no dejar una orden no-pagada con
        // una fecha de pago residual de un estado anterior.
        $order->payment_at = (int) $request->condition === 4
            ? Carbon::parse($request->payment)
            : null;

        $order->condition_id = $request->condition;
        $order->method_id = $request->methods;
        $order->update();

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => [
                'slack' => $order->slack,
            ],
        ]);

    }

    public function destroy($slack)
    {
        $order = Order::slack($slack);

        $this->authorize('delete', $order);

        $user = $order->user->slack;
        $order->delete();

        return redirect()->route('managers.users.orders', $user);

    }
}
