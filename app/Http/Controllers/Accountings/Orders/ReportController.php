<?php

namespace App\Http\Controllers\Accountings\Orders;

use App\Exports\Accountings\Orders\OrdersExport;
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

        $distributors = Distributor::available()->get()->pluck('title', 'id')->prepend('Todas', '0');
        $conditions = OrderCondition::available()->get()->pluck('title', 'id')->prepend('Todas', '0');
        $types = OrderType::available()->get()->pluck('title', 'id')->prepend('Todas', '0');
        $methods = OrderMethod::available()->get()->pluck('title', 'id')->prepend('Todas', '0');

        return view('accountings.views.orders.report.index')->with([
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
        $date = explode(' - ', $request->range);
        $start = Carbon::parse($date[0])->startOfDay();
        $end = Carbon::parse($date[1])->endOfDay();

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
