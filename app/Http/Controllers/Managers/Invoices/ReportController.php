<?php

namespace App\Http\Controllers\Managers\Invoices;

use App\Exports\Accountings\Invoices\InvoicesExport;
use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceMethod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function report()
    {

        $distributors = Distributor::get();
        $distributors = $distributors->pluck('title', 'id');
        $distributors->prepend('Todos', '0');

        $methods = InvoiceMethod::latest()->get();
        $methods->prepend('', '');
        $methods = $methods->pluck('label', 'id');

        $conditions = InvoiceCondition::latest()->get();
        $conditions->prepend('', '');
        $conditions = $conditions->pluck('label', 'id');

        return view('managers.views.invoices.reports.index')->with([
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
