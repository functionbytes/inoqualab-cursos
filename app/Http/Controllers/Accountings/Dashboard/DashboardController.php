<?php

namespace App\Http\Controllers\Accountings\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use App\Models\Invoice\Invoice;
use App\Models\Order\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function dashboard()
    {

        // Variables generales
        $currentDate = Carbon::now();
        $startDate = $currentDate->copy()->subMonths(3)->startOfMonth();
        $endDate = $currentDate->copy()->addMonths(2)->endOfMonth();

        // Totales de tarjetas: no necesitan ser exactos al segundo, se cachean
        // para evitar 5 COUNT() sobre tablas de decenas de miles de filas en
        // cada carga del dashboard.
        $totals = Cache::remember('accounting.dashboard.totals', 300, function () {
            return [
                'customers' => User::where('role', 'customer')->count(),
                'enterprises' => Enterprise::count(),
                'users' => User::count(),
                'totalOrders' => Order::count(),
                'totalInvoices' => Invoice::count(),
            ];
        });

        // Facturas del año actual: se usan tanto para el resumen anual (yearInvoices)
        // como para la tabla de detalle (monthlyInvoices) — es el mismo dataset,
        // antes se consultaba 3 veces. Eager-load evita el N+1 de la vista
        // (distributor/condition/method por cada fila de la tabla).
        $yearInvoices = Invoice::whereYear('created_at', $currentDate->year)
            ->with(['distributor', 'condition', 'method'])
            ->get();
        $monthlyInvoices = $yearInvoices;

        $months = [
            'Jan' => 0, 'Feb' => 0, 'Mar' => 0, 'Apr' => 0,
            'May' => 0, 'Jun' => 0, 'Jul' => 0, 'Aug' => 0,
            'Sep' => 0, 'Oct' => 0, 'Nov' => 0, 'Dec' => 0,
        ];

        $monthTranslations = [
            'Jan' => 'Enero', 'Feb' => 'Febrero', 'Mar' => 'Marzo', 'Apr' => 'Abril',
            'May' => 'Mayo', 'Jun' => 'Junio', 'Jul' => 'Julio', 'Aug' => 'Agosto',
            'Sep' => 'Septiembre', 'Oct' => 'Octubre', 'Nov' => 'Noviembre', 'Dec' => 'Diciembre',
        ];

        $currentMonthIndex = Carbon::now()->month - 1; // Índice de 0 a 11

        foreach ($monthlyInvoices as $invoice) {
            $month = Carbon::parse($invoice->created_at)->format('M');
            $months[$month] += 1;
        }

        // Generar el rango de meses con su respectivo año
        $monthKeys = array_keys($months);
        $yearsInRange = collect($months)->mapWithKeys(function ($count, $month) use ($monthTranslations) {
            $translatedMonth = $monthTranslations[$month]; // Traducir mes y añadir el año

            return [$translatedMonth => $count];
        });

        $monthsInRange = collect(range($currentMonthIndex - 2, $currentMonthIndex + 2))
            ->map(function ($index) {
                return ($index + 12) % 12; // Asegurar que el índice esté entre 0 y 11
            })
            ->mapWithKeys(function ($index) use ($months, $monthKeys, $monthTranslations) {
                $monthName = $monthKeys[$index];

                return [$monthTranslations[$monthName] => $months[$monthName]]; // Traducir al español
            });

        $lastSixMonths = collect();
        $monthsRange = [];
        for ($i = 3; $i > 0; $i--) {
            $monthsRange[Carbon::now()->subMonths($i)->format('M')] = 0;
        }
        $monthsRange[$currentDate->format('M')] = 0;
        for ($i = 1; $i <= 2; $i++) {
            $monthsRange[Carbon::now()->addMonths($i)->format('M')] = 0;
        }

        $invoicesInRange = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('MONTH(created_at) as month_number, MONTHNAME(created_at) as month, COUNT(*) as count')
            ->groupBy('month_number', 'month') // Agrupar por mes (número y nombre)
            ->orderBy('month_number') // Ordenar por número del mes
            ->get();

        foreach ($invoicesInRange as $invoice) {
            $monthsRange[$invoice->month] = $invoice->count;
        }
        $lastSixMonths = collect($monthsRange);

        return view('accountings.views.dashboard.index')->with(array_merge($totals, [
            'yearInvoices' => $yearInvoices,
            // La tabla muestra solo las 10 más recientes del año, no el año completo sin paginar.
            'latestInvoices' => $yearInvoices->sortByDesc('created_at')->take(10),
            'viewsSixMonths' => $lastSixMonths,
            'monthValues' => $monthsInRange->values(), // Valores de las facturas
            'monthNames' => $monthsInRange->keys(),
            'yearValues' => $yearsInRange->values(), // Valores de las facturas
            'yearNames' => $yearsInRange->keys(),
        ]));

    }
}
