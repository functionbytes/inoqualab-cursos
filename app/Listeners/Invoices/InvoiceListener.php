<?php

namespace App\Listeners\Invoices;

use App\Events\Invoices\InvoiceCreated;
use App\Mail\Distributors\Invoices\ReportsMails;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class InvoiceListener
{
    public function handle(InvoiceCreated $event): void
    {
        if (filter_var(setting('invoices_notification_email_enable'), FILTER_VALIDATE_BOOLEAN)) {
            $this->handleMailReport($event);
        }
    }

    public function handleMailReport(InvoiceCreated $event)
    {
        $accountings = User::activeUsersWithRole('accounting');

        foreach ($accountings as $accounting) {
            $mail = new ReportsMails($event->invoice, $accounting);
            Mail::to($accounting->email)->queue($mail);
        }

    }
}
