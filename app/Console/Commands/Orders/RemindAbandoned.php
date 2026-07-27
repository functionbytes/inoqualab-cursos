<?php

namespace App\Console\Commands\Orders;

use App\Enums\OrderCondition;
use App\Mail\Customers\Orders\AbandonedOrderMail;
use App\Models\Order\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RemindAbandoned extends Command
{
    protected $signature = 'orders:remind-abandoned {--hours=12}';

    protected $description = 'Envía un recordatorio de pago a las órdenes generadas sin pagar (una sola vez por orden).';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = Carbon::now()->subHours($hours);

        // Órdenes generadas (sin pagar). Solo se recuerda una vez (reminded_at null).
        $orders = Order::query()
            ->where('condition_id', OrderCondition::Generada->value)
            ->whereNull('reminded_at')
            ->where('total_order_amount', '>', 0)
            ->where('created_at', '<', $cutoff)
            ->whereHas('user', fn ($q) => $q->whereNotNull('email'))
            ->with('user')
            ->limit(200)
            ->get();

        $sent = 0;

        foreach ($orders as $order) {
            if (! $order->user?->email) {
                continue;
            }

            try {
                Mail::to($order->user->email)->queue(new AbandonedOrderMail($order));
                $order->reminded_at = Carbon::now()->setTimezone('America/Bogota');
                $order->save();
                $sent++;
            } catch (\Throwable $e) {
                Log::error('orders:remind-abandoned fallo', ['order' => $order->slack, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Recordatorios de pago encolados: {$sent}.");
        Log::info('orders:remind-abandoned', ['sent' => $sent, 'hours' => $hours]);

        return self::SUCCESS;
    }
}
