<?php

namespace App\Console\Commands\Orders;

use App\Enums\OrderCondition;
use App\Http\Controllers\Pages\CheckoutController;
use App\Models\Order\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Red de seguridad del webhook: si `createInscriptions` falla tras marcar la
 * orden como pagada (error transitorio de BD), la orden queda PAGADA pero sin
 * matrícula, y `orders:reconcile-pending` no la recupera (solo mira las
 * pendientes). Este comando detecta órdenes pagadas recientes que tienen ítems
 * pero NINGUNA inscripción que las referencie y las repara.
 *
 * Idempotente: solo procesa órdenes sin inscripción propia, así que no
 * re-aplica renovaciones ya hechas.
 */
class RepairEnrollments extends Command
{
    protected $signature = 'orders:repair-enrollments {--hours=72} {--limit=200}';

    protected $description = 'Crea las matrículas faltantes de órdenes pagadas cuyo enrolamiento falló.';

    public function handle(CheckoutController $checkout): int
    {
        $hours = (int) $this->option('hours');
        $limit = (int) $this->option('limit');

        $orders = Order::query()
            ->where('condition_id', OrderCondition::Pagada->value)
            ->whereNotNull('payment_at')
            ->where('payment_at', '>', Carbon::now()->subHours($hours))
            ->whereHas('items')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('inscriptions')
                    ->whereColumn('inscriptions.order_id', 'orders.id');
            })
            ->limit($limit)
            ->get();

        $repaired = 0;

        foreach ($orders as $order) {
            try {
                $checkout->createInscriptions($order);
                $repaired++;
                Log::warning('orders:repair-enrollments reparó matrículas de orden pagada sin inscripción', [
                    'order' => $order->slack,
                ]);
            } catch (\Throwable $e) {
                Log::error('orders:repair-enrollments fallo', [
                    'order' => $order->slack,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Órdenes pagadas con matrículas reparadas: {$repaired} (de {$orders->count()} candidatas).");
        Log::info('orders:repair-enrollments', ['repaired' => $repaired, 'checked' => $orders->count()]);

        return self::SUCCESS;
    }
}
