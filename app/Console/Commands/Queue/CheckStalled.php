<?php

namespace App\Console\Commands\Queue;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

/**
 * Avisa cuando una cola lleva demasiado tiempo sin vaciarse.
 *
 * El caso que motiva el comando: un `queue:work` arrancado sin `--queue` solo
 * consume la cola por defecto. Los jobs enviados a 'emails', 'mails',
 * 'newsletter' o 'seo' se acumulan en Redis sin fallar, sin entrar en
 * failed_jobs y sin escribir en el log -- no hay ninguna señal de que algo
 * vaya mal. Se encontraron así seis avisos de factura parados.
 *
 * En vez de intentar leer la antigüedad de cada job (el payload de Laravel no
 * lleva timestamp de encolado), se anota en caché el instante en que una cola
 * pasó de vacía a tener trabajo y se borra en cuanto se vacía. Si ese instante
 * envejece más de --minutes, nadie la está consumiendo al ritmo suficiente.
 */
class CheckStalled extends Command
{
    protected $signature = 'queue:check-stalled {--minutes=30 : Minutos que una cola puede llevar con trabajo pendiente antes de avisar}';

    protected $description = 'Avisa si alguna cola lleva demasiado tiempo sin vaciarse (worker caído o arrancado sin --queue).';

    private const CACHE_PREFIX = 'queue.stalled.since.';

    public function handle(): int
    {
        $minutes = max(1, (int) $this->option('minutes'));
        $stalled = [];

        foreach (config('queue.app_queues', ['default']) as $queue) {
            $size = $this->sizeOf($queue);

            if ($size === null) {
                continue;
            }

            $key = self::CACHE_PREFIX.$queue;

            if ($size === 0) {
                Cache::forget($key);
                $this->line(sprintf('  %-12s vacía', $queue));

                continue;
            }

            // Primera vez que se la ve con trabajo: se anota y se espera.
            $since = Cache::get($key);

            if ($since === null) {
                // El TTL sobra con holgura sobre la ventana de alerta para que
                // la marca no caduque justo antes de poder disparar el aviso.
                Cache::put($key, now()->timestamp, now()->addMinutes($minutes * 4));
                $this->line(sprintf('  %-12s %d pendiente(s), empieza a contar', $queue, $size));

                continue;
            }

            // Carbon 3 devuelve float; se trunca para no loguear 90.00653741666666.
            $waiting = (int) now()->diffInMinutes(now()->setTimestamp((int) $since), absolute: true);

            if ($waiting >= $minutes) {
                $stalled[$queue] = ['pending' => $size, 'minutes' => $waiting];
                $this->error(sprintf('  %-12s %d pendiente(s) desde hace %d min', $queue, $size, $waiting));

                continue;
            }

            $this->line(sprintf('  %-12s %d pendiente(s) desde hace %d min', $queue, $size, $waiting));
        }

        if ($stalled === []) {
            $this->info('Todas las colas avanzan.');

            return self::SUCCESS;
        }

        Log::error('queue:check-stalled colas sin consumir', [
            'threshold_minutes' => $minutes,
            'queues' => $stalled,
            'hint' => 'Comprueba que el worker corre con --queue='.implode(',', config('queue.app_queues', [])),
        ]);

        return self::FAILURE;
    }

    /**
     * Devuelve null si la cola no se puede consultar, para no convertir un
     * problema de conexión con el backend en una falsa alerta de atasco.
     */
    private function sizeOf(string $queue): ?int
    {
        try {
            return Queue::size($queue);
        } catch (\Throwable $e) {
            $this->warn(sprintf('  %-12s no se pudo consultar: %s', $queue, $e->getMessage()));

            return null;
        }
    }
}
