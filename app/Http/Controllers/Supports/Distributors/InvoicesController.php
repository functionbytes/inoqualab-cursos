<?php

namespace App\Http\Controllers\Supports\Distributors;

use App\Exports\Distributors\Invoices\InvoicesExport;
use App\Http\Controllers\Controller;
use App\Models\Distributor\Distributor;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceMethod;
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

        // Stats en 1 query con agregación condicional, scopeadas a las
        // facturas de este distribuidor (no a los resultados filtrados de
        // arriba). Los ids de condición se resuelven por slug (no se
        // hardcodean) para no depender del orden de inserción del seeder.
        $conditionIds = InvoiceCondition::whereIn('slug', ['pagada', 'pendiente', 'rechazada'])->pluck('id', 'slug');

        // toBase()->reorder(): la relación invoices() trae su propio
        // ->orderBy('created_at','desc'); sin limpiarlo, MySQL rechaza
        // mezclar columnas agregadas con una columna suelta en ORDER BY
        // sin GROUP BY (error 1140).
        $agg = $distributor->invoices()->toBase()->reorder()->selectRaw(
            'COUNT(*) total,
             SUM(condition_id = ?) paid,
             SUM(condition_id = ?) pending,
             SUM(condition_id = ?) rejected',
            [
                $conditionIds['pagada'] ?? 0,
                $conditionIds['pendiente'] ?? 0,
                $conditionIds['rechazada'] ?? 0,
            ]
        )->first();

        $stats = [
            'total' => (int) $agg->total,
            'paid' => (int) $agg->paid,
            'pending' => (int) $agg->pending,
            'rejected' => (int) $agg->rejected,
        ];

        return view('supports.views.distributors.invoices.invoices.index')->with([
            'invoices' => $invoices,
            'conditions' => $conditions,
            'condition' => $condition,
            'methods' => $methods,
            'method' => $method,
            'searchKey' => $searchKey,
            'stats' => $stats,
            'distributor' => $distributor,
        ]);
    }

    public function detail($slack)
    {

        $invoice = Invoice::slack($slack);

        // El distribuidor puede haberse borrado (soft delete) después de emitir
        // la factura; la vista lee $invoice->distributor->title sin null-check.
        abort_unless($invoice->distributor instanceof Distributor, 404, 'El distribuidor de esta factura ya no existe.');

        $detailsInvoice = $invoice->details()->with('course', 'enterprise')->get();

        $groupedDetails = $detailsInvoice->groupBy(function ($item) {
            return $item->enterprise_id;
        })->map(function ($items) {
            return $items->groupBy('course_id');
        });

        $details = [];

        foreach ($groupedDetails as $enterpriseId => $courses) {
            // La empresa/el curso del detalle pueden haberse borrado (soft
            // delete) después de emitirse la factura.
            $enterprise = $courses->first()->first()->enterprise->title ?? 'N/D';
            $totalEnterprise = 0;

            foreach ($courses as $courseId => $detail) {
                $course = $detail->first()->course->title ?? 'N/D';
                $quantity = $detail->sum('quantity');
                // $amount ya es la suma de los totales de linea de cada
                // InvoiceDetails agrupado (no un precio unitario): volver a
                // multiplicar por $quantity inflaba el total cuadraticamente
                // -- una factura real con 204 inscripciones del mismo curso
                // mostraba $104.040.000 en vez de $510.000 (204x). Managers
                // y Accountings ya calculaban esto bien (totalAmount = $amount).
                $amount = $detail->sum('amount');

                $totalAmount = $amount;
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

        // Mismo guard que detail(): el distribuidor puede haberse borrado
        // (soft delete) después de emitir la factura.
        abort_unless($invoice->distributor instanceof Distributor, 404, 'El distribuidor de esta factura ya no existe.');

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

        $range = parse_date_range($request->range);

        if ($range === null) {
            return back()->with('error', 'Selecciona un rango de fechas válido para generar el reporte.');
        }

        [$start, $end] = $range;

        return Excel::download(new InvoicesExport($distributor, $method, $condition, $start, $end), 'Reporte Facturación.xlsx');

    }
}
