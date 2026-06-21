<?php

namespace App\Jobs\Analytics;

use App\Mail\Analytics\AnalyticsReportMail;
use App\Models\AnalyticsReportSchedule;
use App\Services\AnalyticsReportService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Analytics\Period;

class GenerateAnalyticsReport implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public int $backoff = 30;

    public function __construct(
        protected string $reportType = 'daily',
        protected ?string $email = null,
        protected ?AnalyticsReportSchedule $schedule = null,
    ) {
        if ($schedule) {
            $this->reportType = $schedule->frequency;
            $this->email = $schedule->email;
        }

        $this->onQueue('emails');
    }

    public static function dispatchForSchedule(AnalyticsReportSchedule $schedule): void
    {
        static::dispatch(schedule: $schedule);
    }

    public function uniqueId(): string
    {
        return $this->schedule ? "analytics_report_{$this->schedule->id}" : '';
    }

    public function uniqueFor(): int
    {
        return 300;
    }

    public function handle(AnalyticsReportService $service): void
    {
        if (! setting('google_analytics_property_id') || ! setting('google_analytics_credentials')) {
            Log::warning('Analytics: credenciales no configuradas, reporte omitido.');

            return;
        }

        try {
            $period = $this->getPeriodByType();
            $report = $service->generateReport($period, $this->reportType);

            $service->saveReport($report, $this->reportType);

            if ($this->schedule) {
                $this->sendScheduledEmail($report, $service);
            } elseif ($this->email) {
                $this->sendAdHocEmail($report);
            }

            Log::info('Analytics report generado correctamente', [
                'type' => $this->reportType,
                'schedule_id' => $this->schedule?->id,
                'email' => $this->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics: fallo al generar reporte — '.$e->getMessage(), [
                'schedule_id' => $this->schedule?->id,
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Analytics report job failed', [
            'type' => $this->reportType,
            'schedule_id' => $this->schedule?->id,
            'email' => $this->email,
            'error' => $exception->getMessage(),
        ]);
    }

    protected function getPeriodByType(): Period
    {
        return match ($this->reportType) {
            'weekly' => Period::days(7),
            'monthly' => Period::days(30),
            default => Period::days(1),
        };
    }

    protected function sendScheduledEmail(array $report, AnalyticsReportService $service): void
    {
        $reportPath = null;

        try {
            $reportPath = $service->generateReportFile($report, $this->schedule->format);
            $summary = $service->buildSummary($report);

            Mail::to($this->schedule->email)
                ->send(new AnalyticsReportMail($this->schedule, $reportPath, $summary));

            $this->schedule->update([
                'last_sent_at' => now(),
                'next_run_at' => $service->calculateNextRun($this->schedule->frequency),
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics: fallo al enviar email programado — '.$e->getMessage(), [
                'schedule_id' => $this->schedule->id,
            ]);
        } finally {
            if ($reportPath && file_exists($reportPath)) {
                @unlink($reportPath);
            }
        }
    }

    protected function sendAdHocEmail(array $report): void
    {
        $subject = match ($this->reportType) {
            'weekly' => 'Reporte semanal de Analytics',
            'monthly' => 'Reporte mensual de Analytics',
            default => 'Reporte diario de Analytics',
        };

        $overview = $report['overview'];
        $period = $report['period']['start'].' → '.$report['period']['end'];

        $body = '<body style="font-family:Arial,sans-serif;color:#333;max-width:600px;margin:0 auto;padding:20px;">'
            .'<div style="background:#fff;border-radius:8px;padding:30px;border-top:4px solid #008bce;">'
            .'<h2 style="color:#008bce;margin-top:0;">'.$subject.'</h2>'
            .'<p style="color:#555;">Período: <strong>'.$period.'</strong></p>'
            .'<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
            .'<tr style="background:#008bce;color:#fff;"><th style="padding:10px 14px;text-align:left;">Métrica</th><th style="padding:10px 14px;text-align:right;">Valor</th></tr>'
            .'<tr><td style="padding:9px 14px;">Sesiones</td><td style="padding:9px 14px;text-align:right;font-weight:600;">'.number_format($overview['sessions']).'</td></tr>'
            .'<tr><td style="padding:9px 14px;">Usuarios</td><td style="padding:9px 14px;text-align:right;font-weight:600;">'.number_format($overview['users']).'</td></tr>'
            .'<tr><td style="padding:9px 14px;">Vistas de página</td><td style="padding:9px 14px;text-align:right;font-weight:600;">'.number_format($overview['pageviews']).'</td></tr>'
            .'<tr><td style="padding:9px 14px;">Tasa de rebote</td><td style="padding:9px 14px;text-align:right;font-weight:600;">'.round($overview['bounce_rate'] * 100, 1).'%</td></tr>'
            .'</table></div></body>';

        Mail::html($body, fn ($m) => $m->to($this->email)->subject($subject));
    }
}
