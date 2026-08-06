<?php

namespace Tests\Feature\Console;

use App\Jobs\Analytics\GenerateAnalyticsReport;
use App\Models\AnalyticsReportSchedule;
use App\Services\AnalyticsReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Regresión de analytics:dispatch-schedules. Sin try/catch por schedule
 * dentro del chunk(), un schedule "envenenado" (datos raros, error
 * transitorio) tira una excepción sin capturar que aborta chunk() entero --
 * como next_run_at nunca avanza para ese schedule, sigue "due" y vuelve a
 * fallar cada 15 minutos, para siempre, bloqueando también a todos los
 * schedules de ID mayor que nunca llegan a procesarse.
 */
class DispatchAnalyticsSchedulesTest extends TestCase
{
    use RefreshDatabase;

    private function createSchedule(string $frequency, ?Carbon $dueAt = null): AnalyticsReportSchedule
    {
        return AnalyticsReportSchedule::create([
            'name' => "Schedule {$frequency}",
            'frequency' => $frequency,
            'email' => 'reportes@example.test',
            'format' => 'csv',
            'metrics' => ['sessions'],
            'is_active' => true,
            'next_run_at' => $dueAt ?? Carbon::now()->subMinute(),
        ]);
    }

    public function test_a_poisoned_schedule_does_not_block_schedules_with_a_higher_id(): void
    {
        Queue::fake();

        // El "envenenado" se crea primero (ID menor): chunk() ordena por PK
        // ascendente, así que sin la guarda es justo el que bloquea a los demás.
        // frequency es un enum de BD (daily/weekly/monthly): ambos usan un
        // valor válido, el mock simula la falla por orden de llamada, no por
        // frequency.
        $poisoned = $this->createSchedule('daily');
        $healthy = $this->createSchedule('daily');

        $this->partialMock(AnalyticsReportService::class, function ($mock) {
            $mock->shouldReceive('calculateNextRun')
                ->with('daily')
                ->once()
                ->andThrow(new \RuntimeException('fallo simulado'));
            $mock->shouldReceive('calculateNextRun')
                ->with('daily')
                ->andReturn(Carbon::now()->addDay());
        });

        $this->artisan('analytics:dispatch-schedules')->assertSuccessful();

        // El job se despacha ANTES de calcular next_run_at, así que ambos
        // schedules alcanzan a encolarse -- lo que prueba el aislamiento es
        // que el sano SÍ avanza su next_run_at pese a que el envenenado (ID
        // menor, procesado antes) falló al calcular el suyo.
        Queue::assertPushed(GenerateAnalyticsReport::class, 2);
        $this->assertTrue($healthy->fresh()->next_run_at->isFuture());

        // El envenenado sigue "due": no se le pudo avanzar next_run_at, así
        // que la próxima corrida lo reintentará (en vez de desaparecer).
        $this->assertTrue($poisoned->fresh()->next_run_at->isPast());
    }
}
