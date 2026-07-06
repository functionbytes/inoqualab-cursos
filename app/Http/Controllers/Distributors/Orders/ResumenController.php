<?php

namespace App\Http\Controllers\Distributors\Orders;

use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use App\Models\Order\Order;
use Illuminate\Http\Request;

class ResumenController extends Controller
{
    public function resumen()
    {

        $distributor = app('distributor');

        $enterprises = $distributor->enterprises()->get();
        $enterprises = $enterprises->pluck('title', 'id');
        $enterprises->prepend('Todos', '0');

        return view('distributors.views.orders.resumen.index')->with([
            'enterprises' => $enterprises,
        ]);

    }

    public function generate(Request $request)
    {

        $filters = [
            'enterprise' => $request->enterprise,
            // SIEMPRE el distribuidor autenticado: nunca confiar en un id del
            // request (sin esto, omitir el parámetro devolvía las 44K órdenes
            // de TODOS los distribuidores en vez de solo las propias).
            'distributor' => app('distributor')->id,
            'search' => $request->search,
            'range' => $request->range,
        ];

        $orders = Order::filterOrders($filters);

        if ($request->enterprise && $request->enterprise !== '0') {
            $enterprise = Enterprise::id($request->enterprise)->title;
            $enterprise_id = Enterprise::id($request->enterprise)->id;
        } else {
            $enterprise = 'Todas';
            $enterprise_id = 0;
        }

        return view('distributors.views.orders.resumen.resumen')->with([
            'orders' => $orders,
            'enterprise' => $enterprise,
            'enterprise_id' => $enterprise_id,
            'searchKey' => $request->search,
        ]);

    }
}
