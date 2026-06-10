<?php

namespace App\Http\Controllers\Distributors\Invoices;

use App\Exports\Distributors\Invoices\InvoicesExport;
use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceMethod;
use Carbon\Carbon;
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
        $invoices = $distributor->invoices();
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

        $invoice = Invoice::slack($slack);

        $detailsInvoice = $invoice->details()->with('course', 'enterprise')->get();

        $groupedDetails = $detailsInvoice->groupBy(function ($item) {
            return $item->enterprise_id;
        })->map(function ($items) {
            return $items->groupBy('course_id');
        });

        $details = [];

        foreach ($groupedDetails as $enterpriseId => $courses) {
            $enterprise = $courses->first()->first()->enterprise->title;
            $totalEnterprise = 0;

            foreach ($courses as $courseId => $detail) {
                $course = $detail->first()->course->title;
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

        $invoice = Invoice::slack($slack);
        $orders = $invoice->orders;

        return view('distributors.views.invoices.invoices.view')->with([
            'invoice' => $invoice,
            'orders' => $orders,
        ]);

    }

    public function report()
    {

        $distributors = Distributor::get();
        $distributors = $distributors->pluck('title', 'id');
        $distributors->prepend('Todos', '0');

        $methods = InvoiceMethod::latest()->get();
        $methods = $methods->pluck('title', 'id');
        $methods->prepend('Todos', '0');

        $conditions = InvoiceCondition::latest()->get();
        $conditions = $conditions->pluck('title', 'id');
        $conditions->prepend('Todos', '0');

        return view('distributors.views.invoices.invoices.report')->with([
            'distributors' => $distributors,
            'methods' => $methods,
            'conditions' => $conditions,
        ]);

    }

    public function generate(Request $request)
    {

        $distributor = $request->distributor;
        $method = $request->methods;
        $condition = $request->condition;
        $date = explode(' - ', $request->range);
        $start = Carbon::parse($date[0])->startOfDay();
        $end = Carbon::parse($date[1])->endOfDay();

        return Excel::download(new InvoicesExport($distributor, $method, $condition, $start, $end), 'Reporte Facturación.xlsx');

    }
}
