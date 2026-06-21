<?php

namespace App\Listeners\Inscriptions;

use App\Events\Inscriptions\InscriptionCreated;
use App\Mail\Distributors\Inscriptions\InscriptionsMails;
use App\Mail\Distributors\Inscriptions\ReportsMails;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InscriptionListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';

    public int $tries = 3;

    public int $backoff = 5;

    public function handle(InscriptionCreated $event): void
    {
        $this->handleMailInscription($event);

        if ($event->inscription->user->getEnterprise() && $event->inscription->user->getEnterprise()->inscription_notification == 1) {
            $this->handleMailReport($event);
        }
    }

    public function handleMailInscription(InscriptionCreated $event): void
    {
        $inscription = $event->inscription;
        $email = $inscription->user->email;
        $mail = new InscriptionsMails($inscription);
        Mail::to($email)->queue($mail);
    }

    public function handleMailReport(InscriptionCreated $event): void
    {
        $inscription = $event->inscription;
        $email = $inscription->user->relation?->enterprise?->email;

        if (! $email) {
            return;
        }

        $mail = new ReportsMails($inscription);
        Mail::to($email)->queue($mail);
    }

    public function failed(InscriptionCreated $event, \Throwable $exception): void
    {
        Log::error('Listener failed: '.static::class, ['error' => $exception->getMessage()]);
    }
}
