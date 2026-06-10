<?php

namespace App\Console\Commands\Orders;

use App\Jobs\ProcessIncomingMail;
use App\Services\IncomingMail\MailboxFetcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchIncomingOrders extends Command
{
    protected $signature = 'mail:fetch-orders';

    protected $description = 'Lee el buzón IMAP y despacha jobs para procesar correos de órdenes entrantes';

    public function handle(): int
    {
        $sEnabled = setting('incoming_mail_enabled');
        $enabled = $sEnabled !== '' ? $sEnabled === 'true' : (bool) config('incoming_mail.enabled');

        if (! $enabled) {
            $this->info('Incoming mail processing is disabled.');

            return self::SUCCESS;
        }

        try {
            $mails = (new MailboxFetcher)->fetchUnseen();

            $count = count($mails);

            if ($count === 0) {
                $this->info('No unseen messages found.');

                return self::SUCCESS;
            }

            foreach ($mails as $dto) {
                ProcessIncomingMail::dispatch($dto);
            }

            $this->info("Dispatched {$count} mail(s) for processing.");

            Log::info('mail:fetch-orders dispatched jobs', ['count' => $count]);
        } catch (\Throwable $e) {
            Log::error('mail:fetch-orders failed', ['error' => $e->getMessage()]);
            $this->error('Error fetching incoming orders: '.$e->getMessage());
        }

        return self::SUCCESS;
    }
}
