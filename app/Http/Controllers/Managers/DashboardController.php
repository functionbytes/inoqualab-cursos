<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use App\Models\Blog\Blog;
use App\Models\Contact;
use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Order\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function dashboard(Request $request): View
    {
        $range = $request->input('range', 'last_30_days');

        $contacts = Contact::latest()->take(5)->get();

        return view('managers.views.dashboard.index', compact('range', 'contacts'));
    }

    public function overview(Request $request): JsonResponse
    {
        $range = $request->input('range', 'last_30_days');

        // Los KPIs se recalculan en cada cambio de filtro del selector de rango;
        // 3min de TTL amortigua eso sin hacer que el dashboard se vea "viejo".
        $data = Cache::remember("manager:dashboard:overview:{$range}", 180, function () use ($range) {
            $period = $this->getPeriod($range);
            $previousPeriod = $this->getPreviousPeriod($range);

            return [
                'kpis' => [
                    'courses' => $this->kpiFor(Course::query(), $period, $previousPeriod),
                    'enterprises' => $this->kpiFor(Enterprise::query(), $period, $previousPeriod),
                    'blogs' => $this->kpiFor(Blog::query(), $period, $previousPeriod),
                    'customers' => $this->kpiFor(User::where('role', 'customer'), $period, $previousPeriod),
                    'managers' => $this->kpiFor(User::where('role', 'manager'), $period, $previousPeriod),
                ],
                'orders' => $this->ordersData($period),
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function kpiFor(Builder $query, array $period, array $previous): array
    {
        $total = (clone $query)->count();

        $current = (clone $query)
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->count();

        $previousCount = (clone $query)
            ->whereBetween('created_at', [$previous['start'], $previous['end']])
            ->count();

        $change = $previousCount > 0
            ? round((($current - $previousCount) / $previousCount) * 100, 1)
            : ($current > 0 ? 100.0 : 0.0);

        return [
            'total' => $total,
            'current' => $current,
            'previous' => $previousCount,
            'change' => $change,
            'series' => $this->dailySeries((clone $query), $period),
        ];
    }

    private function dailySeries(Builder $query, array $period): array
    {
        $counts = $query
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'day');

        $series = [];
        $cursor = $period['start']->copy();

        while ($cursor->lte($period['end'])) {
            $series[] = (int) ($counts[$cursor->format('Y-m-d')] ?? 0);
            $cursor->addDay();
        }

        return $series;
    }

    private function ordersData(array $period): array
    {
        // Agregado en SQL en vez de traer cada orden a PHP: con range=this_year
        // esto llegaba a traer miles de filas completas solo para sumar/contar
        // por día (medido: ~2.800 filas con datos reales de dev).
        $rows = Order::query()
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total, SUM(total_after_discount) as amount, SUM(CASE WHEN method_id = 1 THEN 1 ELSE 0 END) as agreement, SUM(CASE WHEN method_id = 2 THEN 1 ELSE 0 END) as online')
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->keyBy('day');

        $categories = [];
        $countSeries = [];
        $amountSeries = [];
        $cursor = $period['start']->copy();

        while ($cursor->lte($period['end'])) {
            $day = $rows->get($cursor->format('Y-m-d'));

            $categories[] = $cursor->format('d M');
            $countSeries[] = (int) ($day->total ?? 0);
            $amountSeries[] = (float) ($day->amount ?? 0);

            $cursor->addDay();
        }

        return [
            'categories' => $categories,
            'count_series' => $countSeries,
            'amount_series' => $amountSeries,
            'total_amount' => (float) $rows->sum('amount'),
            'agreement_count' => (int) $rows->sum('agreement'),
            'online_count' => (int) $rows->sum('online'),
        ];
    }

    private function getPeriod(string $range): array
    {
        $now = Carbon::now();

        return match ($range) {
            'today' => ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()],
            'last_7_days' => ['start' => $now->copy()->subDays(6)->startOfDay(), 'end' => $now->copy()->endOfDay()],
            'last_30_days' => ['start' => $now->copy()->subDays(29)->startOfDay(), 'end' => $now->copy()->endOfDay()],
            'this_month' => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfDay()],
            'last_month' => ['start' => $now->copy()->subMonth()->startOfMonth(), 'end' => $now->copy()->subMonth()->endOfMonth()],
            'this_year' => ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfDay()],
            default => ['start' => $now->copy()->subDays(29)->startOfDay(), 'end' => $now->copy()->endOfDay()],
        };
    }

    private function getPreviousPeriod(string $range): array
    {
        $now = Carbon::now();

        return match ($range) {
            'today' => ['start' => $now->copy()->subDay()->startOfDay(), 'end' => $now->copy()->subDay()->endOfDay()],
            'last_7_days' => ['start' => $now->copy()->subDays(13)->startOfDay(), 'end' => $now->copy()->subDays(7)->endOfDay()],
            'last_30_days' => ['start' => $now->copy()->subDays(59)->startOfDay(), 'end' => $now->copy()->subDays(30)->endOfDay()],
            'this_month' => ['start' => $now->copy()->subMonth()->startOfMonth(), 'end' => $now->copy()->subMonth()->endOfMonth()],
            'last_month' => ['start' => $now->copy()->subMonths(2)->startOfMonth(), 'end' => $now->copy()->subMonths(2)->endOfMonth()],
            'this_year' => ['start' => $now->copy()->subYear()->startOfYear(), 'end' => $now->copy()->subYear()->endOfYear()],
            default => ['start' => $now->copy()->subDays(59)->startOfDay(), 'end' => $now->copy()->subDays(30)->endOfDay()],
        };
    }
}
