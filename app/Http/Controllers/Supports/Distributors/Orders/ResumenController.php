<?php

namespace App\Http\Controllers\Supports\Distributors\Orders;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\Order\Order;
use Illuminate\Http\Request;

class ResumenController extends Controller
{
    public function resumen($slack)
    {

        $distributor = Distributor::slack($slack);

        $enterprises = Enterprise::select('id', 'title')->orderBy('title')->get();
        $enterprises = $enterprises->pluck('title', 'id');
        $enterprises->prepend('Todas', '0');

        return view('supports.views.distributors.orders.resumen.index')->with([
            'enterprises' => $enterprises,
            'distributor' => $distributor,
        ]);

    }

    public function generate(Request $request)
    {

        $filters = [
            'enterprise' => $request->enterprise,
            'distributor' => $request->distributor,
            'search' => $request->search,
            'range' => $request->range,
        ];

        $orders = Order::filterOrders($filters);

        if ($request->enterprise && $request->enterprise !== '0') {
            $selectedEnterprise = Enterprise::id($request->enterprise);
            $enterprise = $selectedEnterprise->title;
            $enterprise_id = $selectedEnterprise->id;
        } else {
            $enterprise = 'Todas';
            $enterprise_id = 0;
        }

        return view('supports.views.distributors.orders.resumen.resumen')->with([
            'orders' => $orders,
            'enterprise' => $enterprise,
            'enterprise_id' => $enterprise_id,
            'searchKey' => $request->search,
        ]);

    }
}
