<?php

namespace App\Http\Controllers\Managers\Invoices;

use App\Exports\Accountings\Invoices\InvoicesExport;
use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceMethod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function report()
    {
        $this->authorize('viewAny', Invoice::class);

        $distributors = Distributor::get();
        $distributors = $distributors->pluck('title', 'id');
        $distributors->prepend('Todos', '0');

        $methods = InvoiceMethod::latest()->get();
        $methods->prepend('', '');
        $methods = $methods->pluck('title', 'id');

        $conditions = InvoiceCondition::latest()->get();
        $conditions->prepend('', '');
        $conditions = $conditions->pluck('title', 'id');

        return view('managers.views.invoices.reports.index')->with([
            'distributors' => $distributors,
            'methods' => $methods,
            'conditions' => $conditions,
        ]);

    }

    public function generate(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $distributor = $request->distributor;
        $method = $request->methods;
        $condition = $request->condition;

        // El rango debe venir como "fecha_inicio - fecha_fin".
        $date = explode(' - ', (string) $request->range);
        if (count($date) !== 2) {
            return back()->with('error', 'Selecciona un rango de fechas válido para generar el reporte.');
        }

        try {
            $start = Carbon::parse($date[0])->startOfDay();
            $end = Carbon::parse($date[1])->endOfDay();
        } catch (\Exception $e) {
            return back()->with('error', 'El rango de fechas ingresado no es válido.');
        }

        return Excel::download(new InvoicesExport($distributor, $method, $condition, $start, $end), 'Reporte Facturación.xlsx');

    }
}
