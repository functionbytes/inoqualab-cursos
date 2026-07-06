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

    /** Tamaño de lote de envío. */
    private const CHUNK_SIZE = 100;

    /** Pausa entre lotes para no saturar/bloquear la cuenta del proveedor SMTP. */
    private const CHUNK_THROTTLE_SECONDS = 2;

    public function __construct(
        private readonly NewsletterCampaign $campaign
    ) {
        // Cola dedicada: una campaña grande no debe acaparar la cola `default`
        // compartida con el resto de la app (notificaciones, confirmaciones...).
        $this->onQueue('newsletter');
    }

    public function handle(): void
    {
        if (! $this->campaign->isSending()) {
            return;
        }

        $sent = 0;
        $failed = 0;

        // Lista destino: si la campaña tiene una, solo sus miembros (que sigan
        // suscritos). Si no, a todos los suscriptores (comportamiento por defecto).
        $query = $this->campaign->newsletter_list_id
            ? $this->campaign->list->subscribers()->getQuery()->where('newsletters.is_active', true)
            : Newsletter::query()->subscribed();

        $query
            ->orderBy('newsletters.id')
            ->chunk(self::CHUNK_SIZE, function ($subscribers) use (&$sent, &$failed) {
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

                // Throttle entre lotes: solo si el lote vino lleno (probablemente
                // hay más), evita pausar innecesariamente en el último lote.
                if ($subscribers->count() === self::CHUNK_SIZE) {
                    sleep(self::CHUNK_THROTTLE_SECONDS);
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
