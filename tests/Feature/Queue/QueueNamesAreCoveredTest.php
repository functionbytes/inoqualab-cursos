<?php

namespace Tests\Feature\Queue;

use Tests\TestCase;

/**
 * Guardarraíl: toda cola con nombre que aparezca en el código tiene que estar
 * declarada en config('queue.app_queues').
 *
 * Por qué importa: `queue:work` sin `--queue` consume ÚNICAMENTE la cola por
 * defecto de la conexión. Un job despachado a una cola que el worker no
 * escucha no falla, no entra en failed_jobs y no deja rastro en el log:
 * se queda pendiente en Redis indefinidamente. Este proyecto ya lo sufrió con
 * la cola 'emails' (InvoiceListener), así que aquí se prefiere romper el CI a
 * descubrirlo semanas después revisando por qué no llegaron los correos.
 */
class QueueNamesAreCoveredTest extends TestCase
{
    /** Colas de librerías de terceros que el worker no tiene por qué atender. */
    private const IGNORED = [];

    public function test_every_queue_used_in_code_is_declared_in_config(): void
    {
        $declared = config('queue.app_queues');

        $this->assertIsArray($declared, 'config/queue.php debe declarar app_queues.');
        $this->assertContains('default', $declared, 'app_queues debe incluir la cola default.');

        $found = $this->queuesUsedInCode();

        $missing = array_diff($found, $declared, self::IGNORED);

        $this->assertSame([], array_values($missing), sprintf(
            "Estas colas se usan en el código pero no están en config('queue.app_queues'): %s\n".
            'Añádelas ahí Y a la lista --queue= del worker (supervisor / Herd), o los jobs se '.
            'quedarán pendientes en silencio para siempre.',
            implode(', ', $missing)
        ));
    }

    public function test_declared_queues_have_no_duplicates_or_blanks(): void
    {
        $declared = config('queue.app_queues');

        $this->assertSame(array_values(array_unique($declared)), array_values($declared), 'app_queues tiene duplicados.');
        $this->assertSame([], array_filter($declared, fn ($q) => ! is_string($q) || trim($q) === ''), 'app_queues tiene entradas vacías.');
    }

    /**
     * Recorre app/ y extrae los nombres literales de cola: onQueue('x'),
     * $queue = 'x' y el default de onQueue(config('...', 'x')).
     *
     * @return list<string>
     */
    private function queuesUsedInCode(): array
    {
        $queues = [];

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path()));

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $code = file_get_contents($file->getPathname());

            // onQueue('emails')  |  onQueue(config('x.queue', 'emails'))
            preg_match_all('/onQueue\(\s*(?:config\([^,]+,\s*)?[\'"]([a-z0-9_-]+)[\'"]/i', $code, $m);
            $queues = array_merge($queues, $m[1]);

            // public string $queue = 'emails';
            preg_match_all('/\$queue\s*=\s*[\'"]([a-z0-9_-]+)[\'"]/i', $code, $m);
            $queues = array_merge($queues, $m[1]);
        }

        sort($queues);

        return array_values(array_unique($queues));
    }

    /**
     * El valor efectivo de config('mailer-module.queue') también acaba en el
     * worker, así que se comprueba aparte: el regex de arriba solo ve el
     * default literal del onQueue(), no lo que el config resuelva de verdad.
     */
    public function test_configured_mailer_queue_is_declared(): void
    {
        $queue = config('mailer-module.queue');

        if ($queue === null) {
            $this->markTestSkipped('mailer-module no define cola.');
        }

        $this->assertContains($queue, config('queue.app_queues'), sprintf(
            "config('mailer-module.queue') = '%s' no está en app_queues: los correos del módulo mailer no se procesarían.",
            $queue
        ));
    }
}
