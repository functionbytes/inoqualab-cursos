<?php

namespace App\Http\Controllers\Supports\Distributors\Orders;

use App\Exports\Supports\Orders\OrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function report($slack)
    {

        $distributor = Distributor::slack($slack);
        $enterprises = $distributor->enterprises()
            ->orderBy('enterprises.created_at', 'desc')
            ->pluck('enterprises.title', 'enterprises.id')
            ->toArray();

        $enterprises = ['0' => 'Todas'] + $enterprises;

        return view('supports.views.distributors.orders.report.report')->with([
            'enterprises' => $enterprises,
            'distributor' => $distributor,
        ]);

    }

    public function generate(Request $request)
    {

        $enterprise = $request->enterprise;
        $distributor = $request->distributor;

        $range = parse_date_range($request->range);

        if ($range === null) {
            return back()->with('error', 'Selecciona un rango de fechas válido para generar el reporte.');
        }

        [$start, $end] = $range;

        return Excel::download(new OrdersExport($enterprise, $distributor, $start, $end), 'Reporte Ordenes.xlsx');

    }
}
