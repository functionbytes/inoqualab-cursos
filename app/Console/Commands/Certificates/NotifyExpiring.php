<?php

namespace App\Console\Commands\Certificates;

use App\Mail\Customers\Certificates\RenewalMail;
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
        }

        $this->info("Certificados que vencen en {$days} días: {$certificates->count()}. Correos de renovación enviados: {$sent}.");
        Log::info('certificates:notify-expiring', ['days' => $days, 'found' => $certificates->count(), 'sent' => $sent]);

        return self::SUCCESS;
    }
}
