<?php

namespace App\Console\Commands\Courses;

use App\Mail\Customers\Courses\AccessExpiringMail;
use App\Models\Inscription;
use App\Models\NewsletterList;
use App\Models\RemarketingRun;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyExpiringAccess extends Command
{
    protected $signature = 'courses:notify-expiring-access {--days=7}';

    protected $description = 'Recuerda a los alumnos cuyo acceso a un curso vence en N días (sin haberlo completado) para que lo terminen o renueven.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $target = Carbon::today()->addDays($days)->toDateString();

        // Guarda contra doble disparo (reintento manual, cron duplicado):
        // sin esto, correr el comando dos veces para el mismo día de cohorte
        // reenvía el recordatorio a todos los accesos por vencer ese día.
        if (RemarketingRun::where('command', 'courses:notify-expiring-access')->where('cohort_date', $target)->exists()) {
            $this->warn("Ya se ejecutó courses:notify-expiring-access para la cohorte {$target}. Nada que hacer.");

            return self::SUCCESS;
        }

        // Solo accesos que vencen ese día y que aún NO se completaron.
        $inscriptions = Inscription::with(['user', 'course'])
            ->whereDate('enroll_expire', $target)
            ->where('culminated', 0)
            ->get();

        $list = NewsletterList::forTrigger('course_access_expiring');

        $sent = 0;
        foreach ($inscriptions as $inscription) {
            if (! $inscription->user || empty($inscription->user->email) || ! $inscription->course) {
                continue;
            }

            try {
                Mail::send(new AccessExpiringMail($inscription));
                $sent++;
            } catch (\Throwable $e) {
                Log::warning('courses:notify-expiring-access mail failed', [
                    'inscription_id' => $inscription->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Lista de campaña: se retira solo cuando el alumno renueva/compra.
            $list?->addByEmail(
                $inscription->user->email,
                $inscription->user->firstname,
                'Acceso por vencer: '.$inscription->course->title
            );
        }

        RemarketingRun::create([
            'command' => 'courses:notify-expiring-access',
            'cohort_date' => $target,
            'found' => $inscriptions->count(),
            'sent' => $sent,
        ]);

        $this->info("Accesos que vencen en {$days} días (sin completar): {$inscriptions->count()}. Recordatorios enviados: {$sent}.");
        Log::info('courses:notify-expiring-access', ['days' => $days, 'found' => $inscriptions->count(), 'sent' => $sent]);

        return self::SUCCESS;
    }
}
