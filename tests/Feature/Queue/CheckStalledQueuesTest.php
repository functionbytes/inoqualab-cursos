<?php

namespace Tests\Feature\Queue;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Comportamiento de `queue:check-stalled`.
 *
 * La lógica que importa es la de la marca temporal: la primera pasada solo
 * anota que la cola tiene trabajo, y solo una pasada posterior -- con esa marca
 * ya envejecida -- puede dar la alarma. Así una cola ocupada un momento no
 * dispara un aviso, pero una que nadie consume acaba delatándose.
 */
class CheckStalledQueuesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function fakeSizes(array $sizes): void
    {
        Queue::shouldReceive('size')->andReturnUsing(fn ($queue) => $sizes[$queue] ?? 0);
    }

    public function test_passes_when_every_queue_is_empty(): void
    {
        $this->fakeSizes([]);

        $this->artisan('queue:check-stalled')
            ->expectsOutputToContain('Todas las colas avanzan.')
            ->assertExitCode(0);
    }

    public function test_first_sighting_only_starts_the_clock(): void
    {
        $this->fakeSizes(['emails' => 3]);

        $this->artisan('queue:check-stalled --minutes=30')
            ->expectsOutputToContain('empieza a contar')
            ->assertExitCode(0);

        $this->assertNotNull(Cache::get('queue.stalled.since.emails'), 'Debería haber anotado desde cuándo espera.');
    }

    public function test_reports_failure_once_the_wait_exceeds_the_threshold(): void
    {
        $this->fakeSizes(['emails' => 6]);

        Cache::put('queue.stalled.since.emails', now()->subMinutes(90)->timestamp, 3600);

        $this->artisan('queue:check-stalled --minutes=30')
            ->expectsOutputToContain('6 pendiente(s) desde hace 90 min')
            ->assertExitCode(1);
    }

    public function test_wait_below_the_threshold_is_not_an_alert(): void
    {
        $this->fakeSizes(['emails' => 6]);

        Cache::put('queue.stalled.since.emails', now()->subMinutes(5)->timestamp, 3600);

        $this->artisan('queue:check-stalled --minutes=30')
            ->expectsOutputToContain('Todas las colas avanzan.')
            ->assertExitCode(0);
    }

    public function test_an_emptied_queue_forgets_its_mark(): void
    {
        Cache::put('queue.stalled.since.emails', now()->subMinutes(90)->timestamp, 3600);

        $this->fakeSizes([]);

        $this->artisan('queue:check-stalled --minutes=30')->assertExitCode(0);

        $this->assertNull(
            Cache::get('queue.stalled.since.emails'),
            'Al vaciarse la cola la marca debe borrarse, o el siguiente pico avisaría al instante.'
        );
    }

    public function test_a_backend_that_cannot_be_queried_is_not_reported_as_stalled(): void
    {
        Queue::shouldReceive('size')->andThrow(new \RuntimeException('connection refused'));

        $this->artisan('queue:check-stalled --minutes=30')
            ->expectsOutputToContain('no se pudo consultar')
            ->assertExitCode(0);
    }
}
