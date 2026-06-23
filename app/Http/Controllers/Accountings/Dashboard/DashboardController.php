<?php

namespace App\Http\Controllers\Accountings\Dashboard;

use App\Enums\OrderCondition;
use App\Http\Controllers\Controller;
use App\Models\Enterprise\Enterprise;
use App\Models\Invoice\Invoice;
use App\Models\Newsletter;
use App\Models\Order\Order;
use App\Models\User;
use App\Structure\Elements;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function dashboard()
    {

        // Variables generales
        $currentDate = Carbon::now();
        $startDate = $currentDate->copy()->subMonths(3)->startOfMonth();
        $endDate = $currentDate->copy()->addMonths(2)->endOfMonth();

        // Clientes y usuarios
        $customers = User::where('role', 'customer')->count();
        $enterprises = Enterprise::count();
        $users = User::count();

        // Pedidos
        $totalOrders = Order::count();
        $onlineOrders = Order::where('method_id', 1)->where('condition_id', OrderCondition::Pagada->value)->count();

        $monthlyOrders = Order::whereYear('created_at', $currentDate->year)
            ->selectRaw('MONTH(created_at) as month_number, MONTHNAME(created_at) as month_name, COUNT(*) as count')
            ->groupBy('month_number', 'month_name')
            ->orderBy('month_number')
            ->get();

        $dailyOrders = Order::where('created_at', '>=', $currentDate->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $monthlyInvoices = Invoice::whereYear('created_at', $currentDate->year)->get();

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
        $monthlyInvoices = Invoice::whereYear('created_at', Carbon::now()->year)->get();

        foreach ($monthlyInvoices as $invoice) {
            $month = Carbon::parse($invoice->created_at)->format('M');
            $months[$month] += 1;
        }

        $currentYear = Carbon::now()->year;
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

        $dailyInvoices = Invoice::where('created_at', '>=', $currentDate->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(total_invoices_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalInvoices = Invoice::count();

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

        $yearInvoices = Invoice::whereYear('created_at', $currentDate->year)->get();

        foreach ($invoicesInRange as $invoice) {
            $monthsRange[$invoice->month] = $invoice->count;
        }
        $lastSixMonths = collect($monthsRange);

        return view('accountings.views.dashboard.index')->with([
            'monthlyOrders' => $monthlyOrders,
            'dailyOrders' => $dailyOrders,
            'totalOrders' => $totalOrders,
            'onlineOrders' => $onlineOrders,
            'yearInvoices' => $yearInvoices,
            'monthlyInvoices' => $monthlyInvoices,
            'dailyInvoices' => $dailyInvoices,
            'totalInvoices' => $totalInvoices,
            'viewsSixMonths' => $lastSixMonths,
            'customers' => $customers,
            'enterprises' => $enterprises,
            'users' => $users,
            'monthValues' => $monthsInRange->values(), // Valores de las facturas
            'monthNames' => $monthsInRange->keys(),
            'yearValues' => $yearsInRange->values(), // Valores de las facturas
            'yearNames' => $yearsInRange->keys(),

        ]);

    }

    public function dashboards()
    {

        $enterprises = Enterprise::latest()->count();
        $users = User::latest()->count();
        $orders = Newsletter::latest()->count();
        $invoices = Invoice::latest()->count();

        $analyticsOrders = Order::where('created_at', '>=', Carbon::now()->startOfYear())->get();

        $analyticsOrders = $analyticsOrders->groupBy(function ($val) {
            return Carbon::parse($val->created_at)->format('M');
        });

        $viewsOrders = new Collection;

        foreach ($analyticsOrders as $order) {
            $element = new Elements;
            $element->number = (int) $order->count();
            $element->date = Carbon::parse($order->first()->created_at)->format('M');
            $viewsOrders->push($element);
        }

        $viewsOrders = $viewsOrders->pluck('number');

        $analyticsEaning = Order::where('created_at', '>=', Carbon::now()->add(-7, 'day')->format('Y-m-d'))->get();

        $analyticsEanings = $analyticsEaning->groupBy(function ($val) {
            return Carbon::parse($val->created_at)->format('d-m');
        });

        $viewsEanings = new Collection;

        foreach ($analyticsEanings as $item) {
            $element = new Elements;
            $element->number = (int) $item->count();
            $element->date = Carbon::parse($item->first()->created_at)->format('d-m');
            $viewsEanings->push($element);
        }

        $monthEanings = $viewsEanings->pluck('date');
        $numberEanings = $viewsEanings->pluck('number');

        return view('managers.views.dashboard.index')->with([
            'viewOrders' => $viewsOrders,
            'analyticsEanings' => $analyticsEanings,
            'analyticsEaning' => $analyticsEaning,
            'numberEanings' => $numberEanings,
            'analyticsOrders' => $analyticsOrders,
            'monthEanings' => $monthEanings,
            'users' => $users,
            'total' => $total,
            'orders' => $orders,
            'online' => $online,
            'enterprises' => $enterprises,
            'agreements' => $agreements,
        ]);

    }
}
