<?php

namespace App\Http\Controllers\Accountings\Distributors;

use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use Illuminate\Http\Request;

class EnterprisesController extends Controller
{
    public function index(Request $request, $slack)
    {

        $searchKey = $request->search;
        $available = $request->available;

        $distributor = Distributor::slack($slack);
        $enterprises = $distributor->enterprises();

        if ($searchKey) {
            $enterprises = $enterprises->where('title', 'like', '%'.$searchKey.'%');
        }

        if ($request->available != null) {
            $enterprises = $enterprises->where('available', $available);
        }

        $enterprises = $enterprises->paginate(paginationNumber());

        return view('accountings.views.distributors.enterprises.index')->with([
            'enterprises' => $enterprises,
            'available' => $available,
            'searchKey' => $searchKey,
        ]);

    }

    public function view($slack)
    {

        $enterprise = Enterprise::slack($slack);

        $availables = collect([
            ['id' => '1', 'label' => 'Publico'],
            ['id' => '0', 'label' => 'Oculto'],
        ]);

        $availables = $availables->pluck('label', 'id');

        return view('accountings.views.distributors.enterprises.view')->with([
            'availables' => $availables,
            'enterprise' => $enterprise,
        ]);

    }

    public function orders(Request $request, $slack)
    {

        $searchKey = $request->search;
        $condition = $request->condition;
        $type = $request->type;
        $method = $request->methods;

        $enterprise = Enterprise::slack($slack);
        $orders = Order::byEnterprise($enterprise->id)->with(['user', 'condition', 'method', 'type']);
        $methods = OrderMethod::latest()->get();
        $conditions = OrderCondition::latest()->get();
        $types = OrderType::latest()->get();

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

        return view('accountings.views.distributors.enterprises.orders')->with([
            'enterprise' => $enterprise,
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
