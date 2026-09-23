<?php

namespace App\Http\Controllers\Customers;

use App\Enums\OrderCondition as Condition;
use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrdersController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = app('customer');
        $searchKey = $request->search;
        $condition = $request->condition;

        // items.itemable lo usa la variante B para listar los cursos de cada
        // pedido; cargarlo aquí evita el N+1 y no penaliza a la variante A.
        $orders = $user->orders()->with(['condition', 'items.itemable'])->latest();
        $conditions = OrderCondition::latest()->get();

        // Conteo por estado para las pestañas de filtro -- independiente de
        // $searchKey/$condition (si no, la pestaña activa "se comería" el
        // conteo de las demás al filtrar la query base).
        $conditionCounts = $user->orders()
            ->selectRaw('condition_id, count(*) as total')
            ->groupBy('condition_id')
            ->pluck('total', 'condition_id');
        $totalOrdersCount = $conditionCounts->sum();

        if ($searchKey) {
            $orders->where('slack', 'like', '%'.$searchKey.'%');
        }

        if ($condition) {
            $orders->where('condition_id', $condition);
        }

        $orders = $orders->paginate(paginationNumber());

        $variant = portalVariant('customers_orders_variant');

        // El filtro por estado, la búsqueda y el paginador se resuelven por
        // AJAX (ver el script en orders/index.blade.php): se devuelve solo el
        // fragmento re-renderizado en vez de la página completa, así la
        // pestaña activa/el buscador no fuerzan un recargo de toda la vista.
        if ($request->ajax()) {
            return response()->json([
                'html' => view('customers.partials.views.orders.list', compact(
                    'orders', 'conditions', 'condition', 'searchKey', 'conditionCounts', 'totalOrdersCount'
                ))->render(),
                'total' => $orders->total(),
                'label' => Str::plural('pedido', $orders->total()),
            ]);
        }

        return view('customers.views.orders.index'.$variant, compact(
            'orders', 'conditions', 'condition', 'searchKey', 'conditionCounts', 'totalOrdersCount'
        ));
    }

    public function view($slack)
    {
        $user = app('customer');
        $order = Order::where('slack', $slack)->where('user_id', $user->id)
            ->with(['inscription', 'condition', 'method', 'coupon', 'activity.distributor', 'activity.enterprise', 'activity.staff'])
            ->firstOrFail();

        // Paginado aparte (no via with('items')): un pedido puede acumular
        // muchos artículos (paquetes con varios cursos, renovaciones, etc.)
        // y listarlos todos sin límite no escala.
        $items = $order->items()
            ->with(['itemable' => fn ($morphTo) => $morphTo->morphWith([Course::class => ['categorie']])])
            ->paginate(10);

        return view('customers.views.orders.view', [
            'order' => $order,
            'items' => $items,
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
        // coupon: invoice.blade.php lo usa para mostrar el descuento aplicado.
        $order = Order::where('slack', $slack)->where('user_id', $user->id)
            ->with(['items.itemable', 'condition', 'method', 'user', 'coupon'])
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
            // getlogo() da una URL (posiblemente de producción, ver
            // getLogoBase64()) que DomPDF no puede cargar de forma confiable
            // sin red habilitada -- se embebe el archivo local como data URI.
            'logo' => getLogoBase64(),
        ]);

        return $pdf->download('recibo-'.($order->reference ?? $order->slack).'.pdf');
    }
}
