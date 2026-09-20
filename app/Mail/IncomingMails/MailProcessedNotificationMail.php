<?php

namespace App\Mail\IncomingMails;

use App\Models\Mail\IncomingMail;
use App\Services\MailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailProcessedNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly IncomingMail $incomingMail
    ) {
        $this->onQueue('emails');
    }

    public function build(): self
    {
        $enterprise = $this->incomingMail->enterprise?->title;
        $orderSlack = $this->incomingMail->order?->slack;

        $requestSummary = $enterprise
            ? "La solicitud de inscripción para <strong style=\"color:#081A28;\">{$enterprise}</strong> ha sido procesada correctamente."
            : 'Tu solicitud de inscripción ha sido procesada correctamente.';

        $referenceBlock = $orderSlack
            ? $this->referenceBlock($orderSlack)
            : '';

        $data = app(MailTemplateService::class)->render('mails.request_processed', [
            'REQUEST_SUMMARY' => $requestSummary,
            'REFERENCE_BLOCK' => $referenceBlock,
            'COURSES_URL' => route('customers.courses'),
        ]);

        return $this->to($this->incomingMail->from)
            ->subject($data['subject'])
            ->html($data['html']);
    }

    private function referenceBlock(string $orderSlack): string
    {
        return <<<HTML
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f9fb;border-radius:10px;margin:0 0 28px;"><tr><td style="padding:22px 24px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td width="150" valign="top" style="padding:0;font-family:'Figtree',Arial,Helvetica,sans-serif;font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:#93a0ad;">REFERENCIA</td>
<td valign="top" style="padding:0;font-family:'Figtree',Arial,Helvetica,sans-serif;font-size:14px;color:#081A28;font-weight:600;">{$orderSlack}</td>
</tr>
</table>
</td></tr></table>
HTML;
    }
}
