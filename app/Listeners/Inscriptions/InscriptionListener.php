<?php

namespace App\Listeners\Inscriptions;

use App\Events\Inscriptions\InscriptionCreated;
use App\Mail\Distributors\Inscriptions\InscriptionsMails;
use App\Mail\Distributors\Inscriptions\ReportsMails;
use Illuminate\Support\Facades\Mail;

class InscriptionListener
{
    public function handle(InscriptionCreated $event): void
    {
        $this->handleMailInscription($event);

        if ($event->inscription->user->getEnterprise() && $event->inscription->user->getEnterprise()->inscription_notification == 1) {
            $this->handleMailReport($event);
        }

    }

    public function handleMailInscription(InscriptionCreated $event)
    {

        $inscription = $event->inscription;
        $email = $inscription->user->email;
        $mail = new InscriptionsMails($inscription);
        Mail::to($email)->queue($mail);
    }

    public function handleMailReport(InscriptionCreated $event)
    {

        $inscription = $event->inscription;
        $email = $inscription->user->relation?->enterprise?->email;

        if (! $email) {
            return;
        }

        $mail = new ReportsMails($inscription);
        Mail::to($email)->queue($mail);

    }
}
