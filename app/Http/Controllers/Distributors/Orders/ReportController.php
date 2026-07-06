<?php

namespace App\Http\Controllers\Distributors\Orders;

use App\Exports\Distributors\Orders\OrdersExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function report()
    {

        $distributor = app('distributor');

        $enterprises = $distributor->enterprises()->get()->pluck('title', 'id')->prepend('Todas', '0');

        return view('distributors.views.orders.report.report')->with([
            'enterprises' => $enterprises,
        ]);

    }

    public function generate(Request $request)
    {

        // SIEMPRE el distribuidor autenticado: nunca confiar en un id del
        // request (evita descargar órdenes de otro distribuidor).
        $distributor = app('distributor')->id;
        $enterprise = $request->enterprise;
        $date = explode(' - ', $request->range);
        $start = Carbon::parse($date[0])->startOfDay();
        $end = Carbon::parse($date[1])->endOfDay();

        return Excel::download(new OrdersExport($enterprise, $distributor, $start, $end), 'Reporte Ordenes.xlsx');

    }
}
