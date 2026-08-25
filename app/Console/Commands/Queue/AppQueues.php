<?php

namespace App\Console\Commands\Queue;

use Illuminate\Console\Command;

/**
 * Imprime la lista de colas de la app lista para pasarla a `queue:work --queue=`.
 *
 * Existe para que el arranque del worker no tenga que repetir la lista a mano
 * (y quedarse desactualizado en cuanto se añada una cola):
 *
 *   php artisan queue:work redis --queue=$(php artisan queue:app-queues)
 */
class AppQueues extends Command
{
    protected $signature = 'queue:app-queues';

    protected $description = 'Imprime las colas de la aplicación separadas por coma, para --queue= del worker.';

    public function handle(): int
    {
        $this->getOutput()->write(implode(',', config('queue.app_queues', ['default'])));

        return self::SUCCESS;
    }
}
