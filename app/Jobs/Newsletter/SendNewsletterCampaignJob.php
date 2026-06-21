<?php

namespace App\Jobs\Newsletter;

use App\Mail\Newsletter\NewsletterCampaignMail;
use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewsletterCampaignJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 3600;

    public int $backoff = 60;

    public function __construct(
        private readonly NewsletterCampaign $campaign
    ) {
        $this->onQueue(config('queue.connections.redis.queue', 'default'));
    }

    public function handle(): void
    {
        if (! $this->campaign->isSending()) {
            return;
        }

        $sent = 0;
        $failed = 0;

        Newsletter::query()
            ->subscribed()
            ->orderBy('id')
            ->chunk(100, function ($subscribers) use (&$sent, &$failed) {
                foreach ($subscribers as $subscriber) {
                    try {
                        Mail::send(new NewsletterCampaignMail($this->campaign, $subscriber));
                        $sent++;
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::warning('Newsletter campaign email failed', [
                            'campaign_id' => $this->campaign->id,
                            'email' => $subscriber->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        $this->campaign->update([
            'status' => 'sent',
            'sent_count' => $sent,
            'failed_count' => $failed,
            'sent_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->campaign->update(['status' => 'failed']);

        Log::error('Newsletter campaign job failed', [
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
