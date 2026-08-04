<?php

namespace App\Http\Controllers\Distributors\Invoices;

use App\Exports\Distributors\Invoices\InvoicesExport;
use App\Http\Controllers\Controller;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceMethod;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InvoicesController extends Controller
{
    public function index(Request $request)
    {

        $searchKey = $request->search;
        $condition = $request->condition;
        $type = $request->type;
        $method = $request->methods;

        $distributor = app('distributor');
        $invoices = $distributor->invoices()->with(['distributor', 'condition', 'method']);
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

        if ($type) {
            $invoices = $invoices->where('type_id', $type);
        }

        $invoices = $invoices->paginate(paginationNumber());

        return view('distributors.views.invoices.invoices.index')->with([
            'invoices' => $invoices,
            'conditions' => $conditions,
            'condition' => $condition,
            'methods' => $methods,
            'method' => $method,
            'searchKey' => $searchKey,
        ]);
    }

    public function detail($slack)
    {

        $invoice = app('distributor')->invoices()->where('slack', $slack)->firstOrFail();

        $detailsInvoice = $invoice->details()->with('course', 'enterprise')->get();

        $groupedDetails = $detailsInvoice->groupBy(function ($item) {
            return $item->enterprise_id;
        })->map(function ($items) {
            return $items->groupBy('course_id');
        });

        $details = [];

        foreach ($groupedDetails as $enterpriseId => $courses) {
            // La empresa/el curso del detalle pueden haberse borrado (soft
            // delete) después de emitirse la factura.
            $enterprise = $courses->first()->first()->enterprise->title ?? 'N/D';
            $totalEnterprise = 0;

            foreach ($courses as $courseId => $detail) {
                $course = $detail->first()->course->title ?? 'N/D';
                $quantity = $detail->sum('quantity');
                $amount = $detail->sum('amount');

                $totalAmount = $quantity * $amount;
                $totalEnterprise += $totalAmount;

                $details[$enterprise][] = [
                    'course' => $course,
                    'quantity' => $quantity,
                    'amount' => $amount,
                    'totalAmount' => $totalAmount,
                ];
            }

            $details[$enterprise]['totalEnterprise'] = $totalEnterprise;

        }

        return view('distributors.views.invoices.invoices.details')->with([
            'invoice' => $invoice,
            'details' => $details,
        ]);
    }

    public function view($slack)
    {

        $invoice = app('distributor')->invoices()->where('slack', $slack)->firstOrFail();
        $orders = $invoice->orders;

        return view('distributors.views.invoices.invoices.view')->with([
            'invoice' => $invoice,
            'orders' => $orders,
        ]);

    }

    public function report()
    {

        $methods = InvoiceMethod::latest()->get();
        $methods = $methods->pluck('title', 'id');
        $methods->prepend('Todos', '0');

        $conditions = InvoiceCondition::latest()->get();
        $conditions = $conditions->pluck('title', 'id');
        $conditions->prepend('Todos', '0');

        return view('distributors.views.invoices.invoices.report')->with([
            'methods' => $methods,
            'conditions' => $conditions,
        ]);

    }

    public function generate(Request $request)
    {

        // SIEMPRE el distribuidor autenticado: nunca confiar en un id del
        // request (evita descargar la facturación de otro distribuidor).
        $distributor = app('distributor')->id;
        $method = $request->methods;
        $condition = $request->condition;
        $range = parse_date_range($request->range);

        if ($range === null) {
            return back()->with('error', 'Selecciona un rango de fechas válido para generar el reporte.');
        }

        [$start, $end] = $range;

        return Excel::download(new InvoicesExport($distributor, $method, $condition, $start, $end), 'Reporte Facturación.xlsx');

    }
}
