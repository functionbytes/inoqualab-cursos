<?php

namespace App\Console\Commands\Certificates;

use App\Mail\Customers\Certificates\RenewalMail;
use App\Models\NewsletterList;
use App\Models\RemarketingRun;
use App\Models\Users\Certificate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyExpiring extends Command
{
    protected $signature = 'certificates:notify-expiring {--days=30}';

    protected $description = 'Notifica a los clientes cuyos certificados vencen en N días para que renueven su curso.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $target = Carbon::today()->addDays($days)->toDateString();

        $certificates = Certificate::with(['user', 'course'])
            ->whereDate('end_at', $target)
            ->get();

        $list = NewsletterList::trigger('certificate_expiring');

        $sent = 0;
        foreach ($certificates as $certificate) {
            if (! $certificate->user || empty($certificate->user->email) || ! $certificate->course) {
                continue;
            }

            try {
                Mail::send(new RenewalMail($certificate));
                $sent++;
            } catch (\Throwable $e) {
                Log::warning('certificates:notify-expiring mail failed', [
                    'certificate_id' => $certificate->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Lista de campaña: se retira solo cuando el alumno renueva/compra.
            $list?->addByEmail(
                $certificate->user->email,
                $certificate->user->firstname,
                'Certificado por vencer: '.$certificate->course->title
            );
        }

        RemarketingRun::create([
            'command' => 'certificates:notify-expiring',
            'cohort_date' => $target,
            'found' => $certificates->count(),
            'sent' => $sent,
        ]);

        $this->info("Certificados que vencen en {$days} días: {$certificates->count()}. Correos de renovación enviados: {$sent}.");
        Log::info('certificates:notify-expiring', ['days' => $days, 'found' => $certificates->count(), 'sent' => $sent]);

        return self::SUCCESS;
    }
}
