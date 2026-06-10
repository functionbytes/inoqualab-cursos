<?php

namespace App\Jobs;

use App\Services\IncomingMail\IncomingMailProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessIncomingMail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public int $backoff = 10;

    /**
     * @param  array<string, mixed>  $mail  Raw DTO from MailboxFetcher::fetchUnseen()
     */
    public function __construct(
        private readonly array $mail
    ) {
        $this->onQueue('mails');
    }

    public function handle(IncomingMailProcessor $processor): void
    {
        $processor->processRaw($this->mail);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessIncomingMail job failed', [
            'message_id' => $this->mail['message_id'] ?? 'unknown',
            'from' => $this->mail['from'] ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);
    }
}
