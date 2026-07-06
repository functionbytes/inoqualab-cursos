<?php

namespace App\Console\Commands\Courses;

use App\Mail\Customers\Courses\CompletedCourseMail;
use App\Models\Inscription;
use App\Models\NewsletterList;
use App\Models\RemarketingRun;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Remarketing post-completación: envía el correo de cross-sell a los alumnos que
 * completaron un curso el día indicado (por defecto, ayer). Trabaja sobre un
 * único día de cohorte para NO reenviar a la base histórica de completaciones.
 */
class NotifyCompleted extends Command
{
    protected $signature = 'courses:notify-completed {--days=1}';

    protected $description = 'Envía recomendaciones de nuevos cursos a los alumnos que completaron un curso hace N días.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $target = Carbon::today()->subDays($days)->toDateString();

        $inscriptions = Inscription::with(['user', 'course'])
            ->where('culminated', 1)
            ->whereDate('enroll_culminated', $target)
            ->get();

        $list = NewsletterList::trigger('course_completed');

        $sent = 0;
        foreach ($inscriptions as $inscription) {
            if (! $inscription->user || empty($inscription->user->email) || ! $inscription->course) {
                continue;
            }

            try {
                Mail::send(new CompletedCourseMail($inscription));
                $sent++;
            } catch (\Throwable $e) {
                Log::warning('courses:notify-completed mail failed', [
                    'inscription_id' => $inscription->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Lista de campaña: se retira solo cuando el alumno compra otro curso.
            $list?->addByEmail(
                $inscription->user->email,
                $inscription->user->firstname,
                'Completó: '.$inscription->course->title
            );
        }

        RemarketingRun::create([
            'command' => 'courses:notify-completed',
            'cohort_date' => $target,
            'found' => $inscriptions->count(),
            'sent' => $sent,
        ]);

        $this->info("Cursos completados el {$target}: {$inscriptions->count()}. Correos de recomendación enviados: {$sent}.");
        Log::info('courses:notify-completed', ['target' => $target, 'found' => $inscriptions->count(), 'sent' => $sent]);

        return self::SUCCESS;
    }
}
