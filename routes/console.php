<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// withoutOverlapping(): sin esto, relanzar el comando a mano mientras el cron
// mensual también corre podía generar facturas duplicadas por distribuidor
// (ver también el lockForUpdate() añadido en Invoices::handle()).
Schedule::command('invoices:generate')->monthlyOn(1, '08:00')->withoutOverlapping();
Schedule::command('notification:autodelete')->daily();
Schedule::command('customers:inactive_delete')->daily();
Schedule::command('orders:reconcile-pending')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('orders:remind-abandoned --hours=12')->dailyAt('09:00');
// Captura más temprana que orders:remind-abandoned: correo dejado en el checkout
// sin llegar a generar la orden (candidato típico de tráfico de pauta). Se corre
// más seguido porque la ventana de recuperación es más corta.
Schedule::command('orders:remind-incomplete-carts --hours=1')->hourly()->withoutOverlapping();
Schedule::command('orders:cleanup-abandoned')->daily();
// Red de seguridad: repara matrículas de órdenes pagadas cuyo enrolamiento falló.
Schedule::command('orders:repair-enrollments')->hourly()->withoutOverlapping();
// Aviso de renovación de certificados: 30 y 7 días antes del vencimiento.
Schedule::command('certificates:notify-expiring --days=30')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('certificates:notify-expiring --days=7')->dailyAt('08:05')->withoutOverlapping();
// Cross-sell post-completación: recomienda nuevos cursos a quien completó ayer.
Schedule::command('courses:notify-completed --days=1')->dailyAt('09:30')->withoutOverlapping();

// Recordatorio de acceso por vencer (7 días antes, sin completar) → renovar.
Schedule::command('courses:notify-expiring-access --days=7')->dailyAt('09:15')->withoutOverlapping();
Schedule::command('mail:fetch-orders')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('mails:reparse-failed')->hourly()->withoutOverlapping();
Schedule::command('analytics:dispatch-schedules')->everyFifteenMinutes()->withoutOverlapping()->onOneServer();
// Poda del audit trail (Spatie activitylog) según retención de config/activitylog.php (365 días).
Schedule::command('activitylog:clean')->dailyAt('03:00')->withoutOverlapping();
// Cadenas de redirect + Core Web Vitals pobres -> seo_alerts (score_drop y
// new_404 se levantan solos en el momento en que ocurren, no necesitan cron).
Schedule::command('seo:check-alerts')->dailyAt('04:00')->withoutOverlapping();

// Vigilancia de las colas: avisa si alguna acumula trabajo sin que nadie lo
// consuma. Cubre el fallo más silencioso de todos -- un worker arrancado sin
// --queue procesa solo 'default' y deja el resto pendiente indefinidamente,
// sin errores ni entradas en failed_jobs. Ver config/queue.php → app_queues.
Schedule::command('queue:check-stalled --minutes=30')->everyThirtyMinutes()->withoutOverlapping();
