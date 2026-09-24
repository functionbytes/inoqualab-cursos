<?php

namespace App\Http\Controllers\Distributors\Orders;

use App\Html\DocumentFormat;
use App\Http\Controllers\Controller;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrdersController extends Controller
{
    public function index(Request $request): View
    {
        $searchKey = $request->search ?? null;
        $condition = $request->condition ?? null;
        $type = $request->type ?? null;
        $method = $request->methods ?? null;

        $distributor = app('distributor');
        $orders = $distributor->ordersActititys()->descending()->with(['user', 'activity.enterprise']);

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

        return view('distributors.views.orders.orders.index')->with([
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

    public function view($slack): View
    {
        // Ownership: solo órdenes que pertenecen al distribuidor autenticado
        // (misma relación que alimenta el listado). Evita IDOR por slack.
        $order = app('distributor')->ordersActititys()
            ->where('orders.slack', $slack)
            ->firstOrFail();

        if ($design = DocumentFormat::design()) {
            return view('managers.views.documents.page', [
                'kind' => 'order',
                'design' => $design,
                'order' => $order,
                'title' => 'Orden '.$order->slack,
                'breadcrumbs' => [['label' => 'Órdenes', 'url' => route('distributor.orders')], ['label' => $order->slack]],
                'actions' => [['label' => 'Imprimir', 'url' => route('distributor.orders.print', $order->slack), 'newTab' => true]],
                'links' => [],
            ]);
        }

        return view('distributors.views.orders.orders.view')->with([
            'order' => $order,
        ]);
    }

    public function print($slack)
    {
        // Ownership: misma relación que view()/index() (evita IDOR por slack).
        $order = app('distributor')->ordersActititys()
            ->where('orders.slack', $slack)
            ->firstOrFail();

        $pdf = Pdf::loadView('distributors.views.orders.orders.print', [
            'order' => $order,
            'brand' => setting('page_title') ?: 'INOQUALAB',
            // getlogo() da una URL (posiblemente de producción) que DomPDF no
            // puede cargar sin red habilitada -- se embebe el archivo local
            // como data URI (mismo patron que customers.views.orders.invoice).
            'logo' => getLogoBase64(),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('order-details.pdf');
    }
}
