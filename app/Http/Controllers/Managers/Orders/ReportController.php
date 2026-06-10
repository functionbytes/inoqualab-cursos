<?php

namespace App\Http\Controllers\Managers\Orders;

use App\Exports\Managers\Orders\OrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function report()
    {

        $distributors = Distributor::available()->get();
        $distributors = $distributors->pluck('title', 'id');
        $distributors->prepend('Todos', '0');

        $conditions = OrderCondition::available()->get();
        $conditions = $conditions->pluck('title', 'id');
        $conditions->prepend('Todas', '0');

        $types = OrderType::available()->get();
        $types = $types->pluck('title', 'id');
        $types->prepend('Todas', '0');

        $methods = OrderMethod::available()->get();
        $methods = $methods->pluck('title', 'id');
        $methods->prepend('Todas', '0');

        return view('managers.views.orders.report.index')->with([
            'distributors' => $distributors,
            'methods' => $methods,
            'types' => $types,
            'conditions' => $conditions,
        ]);

    }

    public function generate(Request $request)
    {

        $distributor = $request->distributor;
        $enterprise = $request->enterprise;
        $type = $request->type;
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
            return back()->with('error', 'El rango de fechas no es válido.');
        }

        return Excel::download(new OrdersExport($distributor, $enterprise, $type, $method, $condition, $start, $end), 'Reporte Ordenes.xlsx');

    }

    public static function getEnterprises(Request $request)
    {

        if ($request->distributor != null) {
            $enterprises = Distributor::id($request->distributor)->enterprises;

            $formatted_enterprises = [];
            $formatted_enterprises[] = ['id' => 0, 'text' => 'Todas'];
            foreach ($enterprises as $enterprise) {
                $formatted_enterprises[] = ['id' => $enterprise->id, 'text' => $enterprise->title];
            }
        } else {
            $formatted_enterprises = [];
        }

        return \Response::json($formatted_enterprises);
    }
}
