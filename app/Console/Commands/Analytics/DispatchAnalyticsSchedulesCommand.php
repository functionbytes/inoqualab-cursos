<?php

namespace App\Console\Commands\Analytics;

use App\Jobs\Analytics\GenerateAnalyticsReport;
use App\Models\AnalyticsReportSchedule;
use App\Services\AnalyticsReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DispatchAnalyticsSchedulesCommand extends Command
{
    protected $signature = 'analytics:dispatch-schedules';

    protected $description = 'Despacha jobs de reportes analytics cuyo next_run_at ya venció';

    public function __construct(private readonly AnalyticsReportService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $lock = Cache::lock('analytics:dispatch-schedules', 60);

        if (! $lock->get()) {
            $this->warn('Another instance is already running.');

            return self::SUCCESS;
        }

        try {
            return $this->dispatch();
        } finally {
            $lock->release();
        }
    }

    private function dispatch(): int
    {
        $count = 0;

        AnalyticsReportSchedule::query()->due()->chunk(50, function ($schedules) use (&$count) {
            foreach ($schedules as $schedule) {
                // Un schedule con datos raros no debe bloquear el resto: si
                // esto revienta sin capturarse, next_run_at nunca avanza para
                // este schedule y chunk() aborta también los siguientes --
                // el mismo schedule "envenenado" vuelve a fallar cada 15
                // minutos, para siempre, bloqueando a todos los de ID mayor.
                try {
                    GenerateAnalyticsReport::dispatchForSchedule($schedule);

                    $schedule->update(['next_run_at' => $this->service->calculateNextRun($schedule->frequency)]);

                    $this->line("  -> [{$schedule->frequency}] {$schedule->name} -> {$schedule->email}");
                    $count++;
                } catch (\Throwable $e) {
                    Log::error('analytics:dispatch-schedules falló para un schedule', [
                        'schedule_id' => $schedule->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("Schedule #{$schedule->id} ({$schedule->name}): {$e->getMessage()}");
                }
            }
        });

        if ($count === 0) {
            $this->info('No hay reportes pendientes.');

            return self::SUCCESS;
        }

        $this->info("Despachados {$count} reporte(s).");

        return self::SUCCESS;
    }
}
