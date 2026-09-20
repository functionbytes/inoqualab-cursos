<?php

namespace App\Console\Commands\Orders;

use App\Mail\Customers\Orders\IncompleteCartMail;
use App\Models\CartAbandonment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RemindIncompleteCarts extends Command
{
    protected $signature = 'orders:remind-incomplete-carts {--hours=1}';

    protected $description = 'Recuerda por correo a quienes dejaron su correo en el checkout pero nunca llegaron a generar una orden (una sola vez).';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = Carbon::now()->subHours($hours);

        $abandonments = CartAbandonment::query()
            ->pending()
            ->where('created_at', '<', $cutoff)
            ->limit(200)
            ->get();

        $sent = 0;

        foreach ($abandonments as $abandonment) {
            if (empty($abandonment->items)) {
                continue;
            }

            try {
                Mail::to($abandonment->email)->queue(new IncompleteCartMail($abandonment));
                $abandonment->reminded_at = Carbon::now()->setTimezone('America/Bogota');
                $abandonment->save();
                $sent++;
            } catch (\Throwable $e) {
                Log::error('orders:remind-incomplete-carts fallo', ['abandonment' => $abandonment->slack, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Recordatorios de carrito incompleto encolados: {$sent}.");
        Log::info('orders:remind-incomplete-carts', ['sent' => $sent, 'hours' => $hours]);

        return self::SUCCESS;
    }
}
