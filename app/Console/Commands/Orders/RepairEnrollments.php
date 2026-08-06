<?php

namespace App\Console\Commands\Orders;

use App\Enums\OrderCondition;
use App\Http\Controllers\Pages\CheckoutController;
use App\Models\Order\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Red de seguridad del webhook: si `createInscriptions` falla tras marcar la
 * orden como pagada (error transitorio de BD), la orden queda PAGADA pero sin
 * matrícula, y `orders:reconcile-pending` no la recupera (solo mira las
 * pendientes). Este comando detecta órdenes pagadas recientes con ítems sin
 * matricular por completo y repara solo lo que falta.
 *
 * Idempotente: usa CheckoutController::missingCourseIdsForOrder() para
 * calcular exactamente qué cursos le faltan al usuario (directos + bundles
 * expandidos) y solo matricula esos, así no re-dispara la renovación de
 * cursos que la orden ya tenía correctamente matriculados. Antes se excluía
 * cualquier orden con AL MENOS UNA inscripción propia, así que una orden con
 * un curso matriculado y un bundle sin matricular (fallo parcial, o un
 * bundle borrado físicamente) escapaba al repair para siempre.
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
            ->limit($limit)
            ->get();

        $repaired = 0;
        $withGaps = 0;

        foreach ($orders as $order) {
            $missing = $checkout->missingCourseIdsForOrder($order);

            if (empty($missing)) {
                continue;
            }

            $withGaps++;

            try {
                $checkout->enrollMissingCourses($order, $missing);
                $repaired++;
                Log::warning('orders:repair-enrollments reparó matrículas faltantes de orden pagada', [
                    'order' => $order->slack,
                    'course_ids' => $missing,
                ]);
            } catch (\Throwable $e) {
                Log::error('orders:repair-enrollments fallo', [
                    'order' => $order->slack,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Órdenes pagadas con matrículas reparadas: {$repaired} (de {$withGaps} con huecos, {$orders->count()} candidatas revisadas).");
        Log::info('orders:repair-enrollments', ['repaired' => $repaired, 'with_gaps' => $withGaps, 'checked' => $orders->count()]);

        return self::SUCCESS;
    }
}
