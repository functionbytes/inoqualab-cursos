<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Order\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $user = app('customer');
        $searchKey = $request->search;
        $condition = $request->condition;

        $orders = $user->orders()->with(['condition'])->latest();
        $conditions = InvoiceCondition::latest()->get();

        if ($searchKey) {
            $orders->where('slack', 'like', '%'.$searchKey.'%');
        }

        if ($condition) {
            $orders->where('condition_id', $condition);
        }

        $orders = $orders->paginate(paginationNumber());

        return view('customers.views.orders.index', compact(
            'orders', 'conditions', 'condition', 'searchKey'
        ));
    }

    public function view($slack)
    {
        $user = app('customer');
        $order = Order::where('slack', $slack)->where('user_id', $user->id)
            ->with(['items.itemable', 'inscription', 'condition'])
            ->firstOrFail();

        return view('customers.views.orders.view', [
            'order' => $order,
            'user' => $user,
        ]);
    }

    /**
     * Reintenta el pago de una orden existente (generada/pendiente) sin crear una nueva.
     */
    public function payment($slack)
    {
        $user = app('customer');
        $order = Order::where('slack', $slack)->where('user_id', $user->id)->firstOrFail();

        // Ya pagada -> confirmación.
        if ($order->condition_id === 4) {
            return redirect()->route('payments.status', [$order->slack, 'APPROVED']);
        }

        // Sin costo (no debería ocurrir) -> nada que pagar.
        if ($order->total_order_amount <= 0) {
            return redirect()->route('customers.orders')->with('error', 'Esta orden no requiere pago.');
        }

        // Página de pago con ambas opciones (Widget + Web Checkout).
        return redirect()->route('payments.pay', $order->slack);
    }
}
