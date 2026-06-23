<?php

namespace App\Console\Commands\Orders;

use App\Http\Controllers\Pages\CheckoutController;
use App\Enums\OrderCondition;
use App\Models\Order\Order;
use App\Services\WompiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcilePending extends Command
{
    protected $signature = 'orders:reconcile-pending {--minutes=10} {--max-hours=72}';

    protected $description = 'Reconsulta a Wompi el estado real de las órdenes PENDING (PSE/Nequi) que no recibieron webhook.';

    public function handle(WompiService $wompi, CheckoutController $checkout): int
    {
        $minutes = (int) $this->option('minutes');
        $maxHours = (int) $this->option('max-hours');

        // Órdenes pendientes con transacción asignada, dentro de una ventana razonable.
        $orders = Order::query()
            ->where('condition_id', OrderCondition::Pendiente->value)
            ->whereNotNull('transaction')
            ->where('transaction', '!=', '')
            ->where('updated_at', '<', Carbon::now()->subMinutes($minutes))
            ->where('created_at', '>', Carbon::now()->subHours($maxHours))
            ->limit(200)
            ->get();

        $reconciled = 0;

        foreach ($orders as $order) {
            $transaction = $wompi->getTransaction($order->transaction);

            if (! $transaction || empty($transaction['status'])) {
                continue;
            }

            // Solo actuar cuando el estado ya es definitivo (no sigue PENDING).
            if ($transaction['status'] === 'PENDING') {
                continue;
            }

            try {
                $checkout->processOrderStatus(
                    $order->slack,
                    $transaction['id'] ?? $order->transaction,
                    $transaction['status'],
                    isset($transaction['amount_in_cents']) ? (int) $transaction['amount_in_cents'] : null,
                    $transaction['currency'] ?? null,
                    $transaction['payment_method_type'] ?? null,
                );
                $reconciled++;
            } catch (\Throwable $e) {
                Log::error('orders:reconcile-pending fallo', ['order' => $order->slack, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Órdenes pendientes reconciliadas: {$reconciled}.");
        Log::info('orders:reconcile-pending', ['reconciled' => $reconciled, 'checked' => $orders->count()]);

        return self::SUCCESS;
    }
}
