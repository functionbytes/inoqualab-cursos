<?php

namespace App\Http\Controllers\Accountings\Invoices;

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

        $distributors = Distributor::latest()->get()->pluck('title', 'id')->prepend('Todos', '0');
        $methods = InvoiceMethod::latest()->get()->pluck('title', 'id')->prepend('Todos', '0');
        $conditions = InvoiceCondition::latest()->get()->pluck('title', 'id')->prepend('Todos', '0');

        return view('accountings.views.invoices.reports.index')->with([
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

        // El rango debe venir como "fecha_inicio - fecha_fin" (mismo guard que
        // la versión de Managers: sin él, un rango malformado da un 500).
        $date = explode(' - ', (string) $request->range);
        if (count($date) !== 2) {
            return back()->with('error', 'Selecciona un rango de fechas válido para generar el reporte.');
        }

        try {
            $start = Carbon::parse($date[0])->startOfDay();
            $end = Carbon::parse($date[1])->endOfDay();
        } catch (\Exception $e) {
            return back()->with('error', 'El rango de fechas no es válido.');
        }

        return Excel::download(new InvoicesExport($distributor, $method, $condition, $start, $end), 'Reporte Facturación.xlsx');

    }
}
