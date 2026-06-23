<?php

namespace App\Http\Controllers\Customers;

use App\Enums\OrderCondition as Condition;
use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrdersController extends Controller
{
    public function index(Request $request): View
    {
        $user = app('customer');
        $searchKey = $request->search;
        $condition = $request->condition;

        $orders = $user->orders()->with(['condition'])->latest();
        $conditions = OrderCondition::latest()->get();

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
            ->with([
                // Eager-load polimórfico: la categoría solo existe en Course (Bundle no la tiene).
                'items.itemable' => fn ($morphTo) => $morphTo->morphWith([Course::class => ['categorie']]),
                'inscription',
                'condition',
            ])
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
        if ($order->condition_id === Condition::Pagada->value) {
            return redirect()->route('payments.status', [$order->slack, 'APPROVED']);
        }

        // Sin costo (no debería ocurrir) -> nada que pagar.
        if ($order->total_order_amount <= 0) {
            return redirect()->route('customers.orders')->with('error', 'Esta orden no requiere pago.');
        }

        // Página de pago con ambas opciones (Widget + Web Checkout).
        return redirect()->route('payments.pay', $order->slack);
    }

    /**
     * Descarga el recibo de una orden PAGADA en PDF.
     */
    public function invoice($slack)
    {
        $user = app('customer');
        $order = Order::where('slack', $slack)->where('user_id', $user->id)
            ->with(['items.itemable', 'condition', 'method', 'user'])
            ->firstOrFail();

        // Solo se genera recibo de órdenes pagadas.
        if ($order->condition_id !== Condition::Pagada->value) {
            return redirect()->route('customers.orders.view', $order->slack)
                ->with('error', 'El recibo está disponible cuando la orden esté pagada.');
        }

        $pdf = Pdf::loadView('customers.views.orders.invoice', [
            'order' => $order,
            'user' => $order->user,
            'brand' => setting('page_title') ?: 'INOQUALAB',
        ]);

        return $pdf->download('recibo-'.($order->reference ?? $order->slack).'.pdf');
    }
}
