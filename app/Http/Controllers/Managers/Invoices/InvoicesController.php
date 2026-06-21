<?php

namespace App\Http\Controllers\Managers\Invoices;

use App\Events\Invoices\InvoiceCreated;
use App\Http\Controllers\Controller;
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

        $invoices = Invoice::with(['distributor', 'condition', 'method'])->latest()->orderBy('number', 'desc');
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

        return view('managers.views.invoices.invoices.index')->with([
            'invoices' => $invoices,
            'conditions' => $conditions,
            'condition' => $condition,
            'type' => $type,
            'methods' => $methods,
            'method' => $method,
            'searchKey' => $searchKey,
        ]);

    }

    public function print($slack)
    {

        $invoice = Invoice::slack($slack);

        return view('managers.views.invoices.invoices.print')->with([
            'invoice' => $invoice,
        ]);

    }

    public function view($slack)
    {

        $invoice = Invoice::slack($slack);

        return view('managers.views.invoices.invoices.view')->with([
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

        return view('managers.views.invoices.invoices.details')->with([
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

        return view('managers.views.invoices.invoices.edit')->with([
            'invoice' => $invoice,
            'conditions' => $conditions,
            'methods' => $methods,
        ]);

    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('invoices.update'), 403);

        $invoice = Invoice::with('distributor')->slack($request->slack);

        if ($request->condition == 4) {
            $invoice->payment_at = Carbon::parse($request->payment);
        } elseif ($request->condition == 2) {

        } elseif ($request->condition == 3) {
        }

        $invoice->condition_id = $request->condition;
        $invoice->method_id = $request->methods;
        $invoice->update();

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => [
                'slack' => $invoice->slack,
                'distributor' => $invoice->distributor->slack,
            ],
        ]);

    }

    public function create()
    {

        $distributors = Distributor::latest()->get();
        $distributors->prepend('', '');
        $distributors = $distributors->pluck('title', 'slack');

        $methods = InvoiceMethod::latest()->get();
        $methods->prepend('', '');
        $methods = $methods->pluck('title', 'id');

        $conditions = InvoiceCondition::latest()->get();
        $conditions->prepend('', '');
        $conditions = $conditions->pluck('title', 'id');

        return view('managers.views.invoices.invoices.create')->with([
            'distributors' => $distributors,
            'conditions' => $conditions,
            'methods' => $methods,
        ]);

    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('invoices.create'), 403);

        $method = $request->methods ?? null;
        $condition = $request->condition ?? null;
        $date = explode(' - ', $request->range);
        $startDate = Carbon::parse($date[0])->startOfDay();
        $endDate = Carbon::parse($date[1])->endOfDay();

        $distributor = Distributor::slack($request->distributor);
        $items = $distributor->orders()->date($startDate, $endDate)->with('order.items')->get();

        if ($items->count() == 0) {

            return response()->json([
                'success' => false,
                'message' => 'No se encontraron ordenes para facturar',
                'data' => '',
            ]);

        } else {

            $invoice = DB::transaction(function () use ($distributor, $items, $method, $condition, $startDate, $endDate) {
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

                foreach ($items as $item) {

                    $order = $item->order;

                    if ($order) {
                        $total += $order->total_order_amount;

                        foreach ($order->items as $orderItem) {

                            $itemType = $orderItem->item_type;
                            $itemId = $orderItem->item_id;
                            $itemPrice = $orderItem->amount;
                            $orderItemCount = $orderItem->quantity;
                            $orderItemAmount = $itemPrice * $orderItemCount;

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

                $invoice->total_discount_amount = 0;
                $invoice->total_after_discount = $total;
                $invoice->total_before_discount = $total;
                $invoice->total_tax_amount = 0;
                $invoice->total_invoices_amount = $total;
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

                foreach ($items as $item) {
                    $order = $item->order;
                    $enterprise_id = $item->enterprise_id;

                    if ($order) {
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

            event(new InvoiceCreated($invoice));

            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $invoice->slack,
            ]);

        }

    }
}
