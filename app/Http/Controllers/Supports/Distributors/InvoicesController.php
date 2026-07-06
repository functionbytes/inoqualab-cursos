<?php

namespace App\Http\Controllers\Supports\Distributors;

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
    public function index(Request $request, $slack)
    {
        $searchKey = $request->search;
        $condition = $request->condition;
        $type = $request->type;
        $method = $request->methods;
        $distributor = Distributor::slack($slack);

        $methods = InvoiceMethod::latest()->get();
        $conditions = InvoiceCondition::latest()->get();

        // Obtener las facturas asociadas al distribuidor con relación a la tabla de distribuidores
        $invoices = $distributor->invoices()->with(['distributor', 'condition', 'method']);

        if ($searchKey) {
            // Envuelto en un closure: sin esto, el orWhereHas quedaba al mismo
            // nivel que el WHERE distributor_id del scope base y lo anulaba,
            // mezclando facturas de otro distribuidor en los resultados.
            $invoices = $invoices->where(function ($query) use ($searchKey) {
                $query->where('reference', 'like', '%'.$searchKey.'%')
                    ->orWhereHas('distributor', function ($query) use ($searchKey) {
                        $query->where('title', 'like', '%'.$searchKey.'%')
                            ->orWhere('nit', 'like', '%'.$searchKey.'%')
                            ->orWhere('email', 'like', '%'.$searchKey.'%');
                    });
            });
        }

        if ($method) {
            $invoices = $invoices->where('method_id', $method);
        }

        // Filtrar por condición
        if ($condition) {
            $invoices = $invoices->where('condition_id', $condition);
        }

        // Filtrar por tipo de factura
        if ($type) {
            $invoices = $invoices->where('type_id', $type);
        }

        // Paginar los resultados
        $invoices = $invoices->paginate(paginationNumber());

        return view('supports.views.distributors.invoices.invoices.index')->with([
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

        return view('supports.views.distributors.invoices.invoices.details')->with([
            'invoice' => $invoice,
            'details' => $details,
        ]);
    }

    public function view($slack)
    {

        $invoice = Invoice::slack($slack);
        $orders = $invoice->orders;

        return view('supports.views.distributors.invoices.invoices.view')->with([
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

        return view('supports.views.distributors.invoices.report.index')->with([
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
