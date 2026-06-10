<?php

namespace App\Http\Controllers\Supports\Distributors\Orders;

use App\Exports\Supports\Orders\OrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use Carbon\Carbon;
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

        return view('supports.views.enterprises.orders.report.index')->with([
            'enterprises' => $enterprises,
            'distributor' => $distributor,
        ]);

    }

    public function generate(Request $request)
    {

        $enterprise = $request->enterprise;
        $distributor = $request->distributor;
        $date = explode(' - ', $request->range);
        $start = Carbon::parse($date[0])->startOfDay();
        $end = Carbon::parse($date[1])->endOfDay();

        return Excel::download(new OrdersExport($enterprise, $distributor, $start, $end), 'Reporte Ordenes.xlsx');

    }
}
