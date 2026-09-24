<?php

namespace App\Http\Controllers\Supports\Distributors\Orders;

use App\Html\DocumentFormat;
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
            // 'condition' agregado al eager-load: la vista ahora pinta el badge
            // de estado por fila (antes no se mostraba), evita N+1 por orden.
            ->with(['user', 'activity.enterprise', 'condition']);

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

        // Stats en 1 query con agregación condicional, scopeadas a las
        // órdenes de este distribuidor (no a los resultados filtrados de
        // arriba). Los ids de condición se resuelven por slug (no se
        // hardcodean) para no depender del orden de inserción del seeder.
        $conditionIds = OrderCondition::whereIn('slug', ['payment', 'pendiente', 'rechazada'])->pluck('id', 'slug');

        // toBase()->reorder(): sin esto, HasManyThrough::get() agrega la
        // columna laravel_through_key al SELECT (para el hydrate de la
        // relación) y MySQL rechaza mezclar columnas agregadas con columnas
        // sueltas sin GROUP BY (error 1140).
        $agg = $distributor->ordersActititys()?->toBase()->reorder()->selectRaw(
            'COUNT(*) total,
             SUM(orders.condition_id = ?) paid,
             SUM(orders.condition_id = ?) pending,
             SUM(orders.condition_id = ?) rejected',
            [
                $conditionIds['payment'] ?? 0,
                $conditionIds['pendiente'] ?? 0,
                $conditionIds['rechazada'] ?? 0,
            ]
        )->first();

        $stats = [
            'total' => (int) ($agg->total ?? 0),
            'paid' => (int) ($agg->paid ?? 0),
            'pending' => (int) ($agg->pending ?? 0),
            'rejected' => (int) ($agg->rejected ?? 0),
        ];

        $view = $request->ajax() ? 'supports.views.distributors.orders.orders._table' : 'supports.views.distributors.orders.orders.index';

        return view($view)->with([
            'orders' => $orders,
            'conditions' => $conditions,
            'condition' => $condition,
            'types' => $types,
            'type' => $type,
            'methods' => $methods,
            'method' => $method,
            'searchKey' => $searchKey,
            'distributor' => $distributor,
            'stats' => $stats,
        ]);
    }

    public function view($slack)
    {

        $order = Order::slack($slack);

        // El cliente puede haberse borrado (soft delete) después de la orden;
        // la vista lee $order->user->firstname sin null-check.
        abort_unless($order->user instanceof User, 404, 'El cliente de esta orden ya no existe.');

        if ($design = DocumentFormat::design()) {
            return view('managers.views.documents.page', [
                'kind' => 'order',
                'design' => $design,
                'order' => $order,
                'title' => 'Orden '.$order->slack,
                'breadcrumbs' => [['label' => 'Órdenes'], ['label' => $order->slack]],
                'actions' => [],
                'links' => [],
            ]);
        }

        return view('supports.views.distributors.orders.orders.view')->with([
            'order' => $order,
        ]);

    }
}
