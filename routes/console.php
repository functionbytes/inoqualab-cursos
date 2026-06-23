<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('invoices:generate')->monthlyOn(1, '08:00');
Schedule::command('notification:autodelete')->daily();
Schedule::command('customers:inactive_delete')->daily();
Schedule::command('orders:reconcile-pending')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('orders:remind-abandoned --hours=12')->dailyAt('09:00');
Schedule::command('orders:cleanup-abandoned')->daily();
// Aviso de renovación de certificados: 30 y 7 días antes del vencimiento.
Schedule::command('certificates:notify-expiring --days=30')->dailyAt('08:00');
Schedule::command('certificates:notify-expiring --days=7')->dailyAt('08:05');
Schedule::command('mail:fetch-orders')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('mails:reparse-failed')->hourly()->withoutOverlapping();
Schedule::command('analytics:dispatch-schedules')->everyFifteenMinutes()->withoutOverlapping()->onOneServer();
// Poda del audit trail (Spatie activitylog) según retención de config/activitylog.php (365 días).
Schedule::command('activitylog:clean')->dailyAt('03:00')->withoutOverlapping();
