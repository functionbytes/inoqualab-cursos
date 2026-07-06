<?php

namespace App\Http\Controllers\Managers\Orders;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use Illuminate\Http\Request;

class ResumenController extends Controller
{
    public function resumen()
    {
        $this->authorize('viewAny', Order::class);

        $distributors = Distributor::available()->get();
        $distributors = $distributors->pluck('title', 'id');
        $distributors->prepend('Todos', '0');

        $conditions = OrderCondition::available()->get();

        $conditions = $conditions->pluck('title', 'id');
        $conditions->prepend('Todas', '0');

        $types = OrderType::available()->get();
        $types = $types->pluck('title', 'id');
        $types->prepend('Todas', '0');

        $methods = OrderMethod::available()->get();
        $methods = $methods->pluck('title', 'id');
        $methods->prepend('Todas', '0');

        return view('managers.views.orders.resumen.index')->with([
            'distributors' => $distributors,
            'methods' => $methods,
            'types' => $types,
            'conditions' => $conditions,
        ]);

    }

    public function generate(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $conditions = OrderCondition::available()->get();
        $types = OrderType::available()->get();
        $methods = OrderMethod::available()->get();

        $filters = [
            'distributor' => $request->distributor,
            'enterprise' => $request->enterprise,
            'search' => $request->search,
            'condition' => $request->condition,
            'type' => $request->type,
            'method' => $request->methods,
            'range' => $request->range,
        ];

        $orders = Order::filterOrders($filters);

        $distributor = ($request->distributor && $request->distributor !== '0')
            ? Distributor::id($request->distributor)
            : null;

        $enterprise = ($request->enterprise && $request->enterprise !== '0')
            ? Enterprise::id($request->enterprise)
            : null;

        return view('managers.views.orders.resumen.resumen')->with([
            'orders' => $orders,
            'distributor' => $distributor,
            'enterprise' => $enterprise,
            'methods' => $methods,
            'method' => $request->methods,
            'types' => $types,
            'type' => $request->type,
            'conditions' => $conditions,
            'condition' => $request->condition,
            'searchKey' => $request->search,
        ]);

    }

    public static function getEnterprises(Request $request)
    {

        if ($request->distributor != null) {
            $enterprises = Distributor::id($request->distributor)->enterprises;

            $formatted_enterprises = [];
            $formatted_enterprises[] = ['id' => 0, 'text' => 'Todas'];
            foreach ($enterprises as $enterprise) {
                $formatted_enterprises[] = ['id' => $enterprise->id, 'text' => $enterprise->title];
            }
        } else {
            $formatted_enterprises = [];
        }

        return \Response::json($formatted_enterprises);

    }
}
