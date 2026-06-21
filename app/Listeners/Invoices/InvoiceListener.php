<?php

namespace App\Listeners\Invoices;

use App\Events\Invoices\InvoiceCreated;
use App\Mail\Distributors\Invoices\ReportsMails;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';

    public int $tries = 3;

    public int $backoff = 5;

    public function handle(InvoiceCreated $event): void
    {
        if (filter_var(setting('invoices_notification_email_enable'), FILTER_VALIDATE_BOOLEAN)) {
            $this->handleMailReport($event);
        }
    }

    public function handleMailReport(InvoiceCreated $event): void
    {
        $accountings = User::activeUsersWithRole('accounting');

        foreach ($accountings as $accounting) {
            $mail = new ReportsMails($event->invoice, $accounting);
            Mail::to($accounting->email)->queue($mail);
        }
    }

    public function failed(InvoiceCreated $event, \Throwable $exception): void
    {
        Log::error('Listener failed: '.static::class, ['error' => $exception->getMessage()]);
    }
}
