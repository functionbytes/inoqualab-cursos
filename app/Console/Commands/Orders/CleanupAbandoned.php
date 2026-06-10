<?php

namespace App\Console\Commands\Orders;

use App\Models\Order\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupAbandoned extends Command
{
    protected $signature = 'orders:cleanup-abandoned {--days=3}';

    protected $description = 'Marca como rechazadas las órdenes "generadas" sin pagar más antiguas que N días.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        // condition_id: 1 = generada (sin pagar). 3 = rechazada.
        $count = Order::where('condition_id', 1)
            ->where('created_at', '<', $cutoff)
            ->update([
                'condition_id' => 3,
                'updated_at' => Carbon::now()->setTimezone('America/Bogota'),
            ]);

        $this->info("Órdenes abandonadas canceladas: {$count} (anteriores a {$cutoff->toDateString()}).");
        Log::info('orders:cleanup-abandoned', ['cancelled' => $count, 'days' => $days]);

        return self::SUCCESS;
    }
}
