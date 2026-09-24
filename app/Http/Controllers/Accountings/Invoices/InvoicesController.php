<?php

namespace App\Http\Controllers\Accountings\Invoices;

use App\Events\Invoices\InvoiceCreated;
use App\Html\DocumentFormat;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accountings\StoreInvoiceRequest;
use App\Http\Requests\Accountings\UpdateInvoiceRequest;
use App\Models\Distributor\Distributor;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceDetails;
use App\Models\Invoice\InvoiceItem;
use App\Models\Invoice\InvoiceMethod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoicesController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $condition = $request->condition;
        $type = $request->type;
        $method = $request->methods;

        $invoices = Invoice::latest()->orderBy('number', 'desc')->with(['distributor', 'condition', 'method']);
        $methods = InvoiceMethod::latest()->get();
        $conditions = InvoiceCondition::latest()->get();

        if ($searchKey) {
            $invoices = $invoices->where('reference', 'like', '%'.$searchKey.'%');
        }

        if ($method) {
            $invoices = $invoices->where('method_id', $method);
        }

        if ($condition) {
            $invoices = $invoices->where('condition_id', $condition);
        }

        $invoices = $invoices->paginate(paginationNumber());

        return view('accountings.views.invoices.invoices.index')->with([
            'invoices' => $invoices,
            'conditions' => $conditions,
            'condition' => $condition,
            'type' => $type,
            'methods' => $methods,
            'method' => $method,
            'searchKey' => $searchKey,
        ]);
    }

    public function view($slack)
    {
        $invoice = Invoice::slack($slack);

        if ($design = DocumentFormat::design()) {
            return view('managers.views.documents.page', [
                'kind' => 'invoice',
                'design' => $design,
                'invoice' => $invoice,
                'title' => 'Factura '.$invoice->reference,
                'breadcrumbs' => [['label' => 'Facturas', 'url' => route('accounting.invoices')], ['label' => $invoice->reference]],
                'actions' => [
                    ['label' => 'Descargar PDF', 'url' => route('accounting.invoices.pdf', $invoice->slack), 'newTab' => true],
                    ['label' => 'Editar factura', 'url' => route('accounting.invoices.edit', $invoice->slack), 'primary' => true],
                ],
                'links' => ['details' => DocumentFormat::link(route('accounting.invoices.details', $invoice->slack))],
            ]);
        }

        return view('accountings.views.invoices.invoices.view')->with([
            'invoice' => $invoice,
        ]);
    }

    public function details($slack)
    {

        $invoice = Invoice::slack($slack);

        $detailsInvoice = $invoice->details()->with('course', 'enterprise')->get();

        $groupedDetails = $detailsInvoice->groupBy(function ($item) {
            return $item->enterprise_id;
        })->map(function ($items) {
            return $items->groupBy('course_id');
        });

        $details = [];

        foreach ($groupedDetails as $enterpriseId => $courses) {
            // Null-safe: una empresa/curso eliminado no debe tumbar la vista
            // (mismo guard que la versión de Managers).
            $enterprise = $courses->first()->first()->enterprise?->title ?? 'Empresa';
            $totalEnterprise = 0;

            foreach ($courses as $courseId => $detail) {
                $course = $detail->first()->course?->title ?? 'Curso';
                $quantity = $detail->sum('quantity');
                $amount = $detail->sum('amount');

                $totalAmount = $amount;
                $totalEnterprise += $amount;

                $details[$enterprise][] = [
                    'course' => $course,
                    'quantity' => $quantity,
                    'amount' => $amount,
                    'totalAmount' => $totalAmount,
                ];
            }

            $details[$enterprise]['totalEnterprise'] = $totalEnterprise;
        }

        if ($design = DocumentFormat::design()) {
            return view('managers.views.documents.page', [
                'kind' => 'details',
                'design' => $design,
                'invoice' => $invoice,
                'details' => $details,
                'title' => 'Reparto de la factura '.$invoice->reference,
                'breadcrumbs' => [
                    ['label' => 'Facturas', 'url' => route('accounting.invoices')],
                    ['label' => $invoice->reference, 'url' => DocumentFormat::link(route('accounting.invoices.view', $invoice->slack))],
                    ['label' => 'Reparto por empresa'],
                ],
                'actions' => [['label' => 'Editar factura', 'url' => route('accounting.invoices.edit', $invoice->slack), 'primary' => true]],
                'links' => ['view' => DocumentFormat::link(route('accounting.invoices.view', $invoice->slack))],
            ]);
        }

        return view('accountings.views.invoices.invoices.details')->with([
            'invoice' => $invoice,
            'details' => $details,
        ]);

    }

    public function edit($slack)
    {

        $invoice = Invoice::slack($slack);

        $methods = InvoiceMethod::latest()->get();
        $methods->prepend('', '');
        $methods = $methods->pluck('title', 'id');

        $conditions = InvoiceCondition::latest()->get();
        $conditions->prepend('', '');
        $conditions = $conditions->pluck('title', 'id');

        return view('accountings.views.invoices.invoices.edit')->with([
            'invoice' => $invoice,
            'conditions' => $conditions,
            'methods' => $methods,
        ]);

    }

    public function update(UpdateInvoiceRequest $request)
    {

        $invoice = Invoice::slack($request->slack);

        if ($request->condition == 4) {
            $invoice->payment_at = Carbon::parse($request->payment);
        } else {
            $invoice->payment_at = null;
        }

        $invoice->condition_id = $request->condition;
        $invoice->method_id = $request->methods;
        $invoice->update();

        $response = [
            'success' => true,
            'message' => 'Success',
            'data' => [
                'slack' => $invoice->slack,
                // El distribuidor puede haberse borrado (soft delete) después
                // de emitir la factura.
                'distributor' => $invoice->distributor?->slack,
            ],
        ];

        return response()->json($response);

    }

    public function create()
    {

        $distributors = Distributor::latest()->get()->pluck('title', 'id')->prepend('', '');
        $methods = InvoiceMethod::latest()->get()->pluck('title', 'id')->prepend('', '');
        $conditions = InvoiceCondition::latest()->get()->pluck('title', 'id')->prepend('', '');

        return view('accountings.views.invoices.invoices.create')->with([
            'distributors' => $distributors,
            'conditions' => $conditions,
            'methods' => $methods,
        ]);

    }

    public function store(StoreInvoiceRequest $request)
    {

        $method = $request->methods ?? null;
        $condition = $request->condition ?? null;
        $date = explode(' - ', $request->range);
        $startDate = Carbon::parse($date[0])->startOfDay();
        $endDate = Carbon::parse($date[1])->endOfDay();

        $distributor = Distributor::id($request->distributor);
        $items = $distributor->orders()->date($startDate, $endDate)->with('order.items')->get();

        if ($items->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron ordenes para facturar',
                'data' => '',
            ]);
        }

        // Toda la facturación (marcar órdenes + crear factura, items y detalles) ocurre en
        // una transacción: si algo falla, no quedan órdenes marcadas como facturadas sin su factura.
        $invoice = DB::transaction(function () use ($items, $distributor, $method, $condition, $startDate, $endDate) {

            $coursesItems = [];
            $total = 0;

            $invoice = new Invoice;
            $invoice->slack = $this->generate_slack('invoices');
            $invoice->number = $this->generate_number('invoices');
            $invoice->reference = setting('invoice_default').$invoice->number;
            $invoice->method_id = $method;
            $invoice->condition_id = $condition;
            $invoice->distributor_id = $distributor->id;
            $invoice->payment_at = null;
            $invoice->notes = '';
            $invoice->available = 1;
            $invoice->from_at = $startDate;
            $invoice->to_at = $endDate;
            $invoice->created_at = Carbon::now()->setTimezone('America/Bogota');
            $invoice->updated_at = Carbon::now()->setTimezone('America/Bogota');

            $seenOrders = [];

            foreach ($items as $item) {

                $order = $item->order;

                // Una orden puede tener varias OrderActivity (una por curso): se
                // agrega su total y sus ítems UNA sola vez para no inflar la factura.
                if ($order && ! isset($seenOrders[$order->id])) {
                    $seenOrders[$order->id] = true;
                    $total += $order->total_order_amount;

                    foreach ($order->items as $orderItem) {

                        $itemType = $orderItem->item_type;
                        $itemId = $orderItem->item_id;
                        $itemPrice = $orderItem->amount;
                        $orderItemCount = $orderItem->quantity;
                        // $orderItem->amount ya es el total de línea (no el precio unitario) —
                        // multiplicarlo por quantity infla el monto en bundles con qty > 1.
                        $orderItemAmount = $itemPrice;

                        if (! isset($coursesItems[$itemType])) {
                            $coursesItems[$itemType] = [];
                        }

                        if (! isset($coursesItems[$itemType][$itemId])) {
                            $coursesItems[$itemType][$itemId] = [
                                'quantity' => 0,
                                'total_amount' => 0,
                                'course_id' => $itemId,
                            ];
                        }

                        $coursesItems[$itemType][$itemId]['quantity'] += $orderItemCount;
                        $coursesItems[$itemType][$itemId]['total_amount'] += $orderItemAmount;
                    }
                }

                $item->invoiced = 1;
                $item->invoiced_at = Carbon::now()->setTimezone('America/Bogota');
                $item->save();
            }

            $totalTaxRate = 0.19;
            $totalTaxAmount = $total * $totalTaxRate;
            $totalAfterDiscount = $total - $totalTaxAmount;
            $invoice->total_tax_amount = $totalTaxAmount;
            $invoice->total_after_discount = $totalAfterDiscount;
            $invoice->total_before_discount = $total;
            $invoice->total_invoices_amount = $total;
            $condition == 4 ? $invoice->payment_at = Carbon::now()->setTimezone('America/Bogota') : null;

            $invoice->save();

            foreach ($coursesItems as $itemType => $itemsById) {
                foreach ($itemsById as $itemId => $data) {
                    $itemInvoice = new InvoiceItem;
                    $itemInvoice->slack = $this->generate_slack('invoice_items');
                    $itemInvoice->course_id = $data['course_id'];
                    $itemInvoice->invoice_id = $invoice->id;
                    $itemInvoice->quantity = $data['quantity'];
                    // La tabla invoice_items tiene subtotal/total (no 'amount'); sin descuento por línea, total = subtotal.
                    $itemInvoice->subtotal = $data['total_amount'];
                    $itemInvoice->total = $data['total_amount'];
                    $itemInvoice->created_at = now();
                    $itemInvoice->updated_at = now();
                    $itemInvoice->save();
                }
            }

            $seenDetails = [];

            foreach ($items as $item) {
                $order = $item->order;
                $enterprise_id = $item->enterprise_id;

                // Misma deduplicación por orden.
                if ($order && ! isset($seenDetails[$order->id])) {
                    $seenDetails[$order->id] = true;
                    foreach ($order->items as $orderItem) {
                        $itemDetail = new InvoiceDetails;
                        $itemDetail->slack = $this->generate_slack('invoice_details');
                        $itemDetail->order_id = $order->id;
                        $itemDetail->course_id = $orderItem->item_id;
                        $itemDetail->invoice_id = $invoice->id;
                        $itemDetail->quantity = $orderItem->quantity;
                        $itemDetail->amount = $orderItem->amount;
                        $itemDetail->enterprise_id = $enterprise_id;
                        $itemDetail->created_at = now();
                        $itemDetail->updated_at = now();
                        $itemDetail->save();
                    }
                }
            }

            return $invoice;
        });

        // El evento (correos/efectos secundarios) se dispara tras confirmar la transacción.
        event(new InvoiceCreated($invoice));

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $invoice->slack,
        ]);

    }
}
