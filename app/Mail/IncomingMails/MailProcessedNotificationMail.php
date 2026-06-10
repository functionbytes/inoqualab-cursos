<?php

namespace App\Mail\IncomingMails;

use App\Models\Mail\IncomingMail;
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
        $siteName = getSetting()->page_title ?? config('app.name');
        $enterprise = $this->incomingMail->enterprise?->title ?? '';
        $orderSlack = $this->incomingMail->order?->slack;
        $coursesUrl = route('customers.courses');
        $logoUrl = getLogo();
        $supportEmail = getSetting()->page_email ?? '';

        $enterpriseLine = $enterprise
            ? "<p style=\"color:#5A7093;line-height:1.7;margin:0 0 16px\">La solicitud de inscripción para <strong>{$enterprise}</strong> ha sido procesada correctamente.</p>"
            : '<p style="color:#5A7093;line-height:1.7;margin:0 0 16px">Tu solicitud de inscripción ha sido procesada correctamente.</p>';

        $orderLine = $orderSlack
            ? "<p style=\"color:#5A7093;line-height:1.7;margin:0 0 24px\">Número de referencia: <strong style=\"color:#081A28\">{$orderSlack}</strong></p>"
            : '';

        $logoHtml = $logoUrl
            ? "<img src=\"{$logoUrl}\" alt=\"{$siteName}\" style=\"max-height:36px;vertical-align:middle\">"
            : "<span style=\"font-size:20px;font-weight:bold;color:#fff\">{$siteName}</span>";

        $footerContact = $supportEmail
            ? "<br>¿Dudas? Escríbenos a <a href=\"mailto:{$supportEmail}\" style=\"color:#008bce\">{$supportEmail}</a>"
            : '';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Solicitud procesada — {$siteName}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:32px 16px">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);max-width:600px;width:100%">

        {{-- Header --}}
        <tr><td style="background:#008bce;padding:24px 32px">
          {$logoHtml}
        </td></tr>

        {{-- Body --}}
        <tr><td style="padding:36px 32px">
          <h2 style="margin:0 0 8px;color:#081A28;font-size:22px">Tu solicitud fue procesada</h2>
          <p style="color:#8D9DB5;font-size:13px;margin:0 0 24px">Notificación automática — {$siteName}</p>

          {$enterpriseLine}
          {$orderLine}

          <a href="{$coursesUrl}"
             style="display:inline-block;background:#008bce;color:#fff;padding:13px 30px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px">
            Ver mis cursos
          </a>
        </td></tr>

        {{-- Divider --}}
        <tr><td style="border-top:1px solid #f0f0f0"></td></tr>

        {{-- Footer --}}
        <tr><td style="padding:20px 32px;background:#f9fafb;color:#8D9DB5;font-size:12px;text-align:center;line-height:1.6">
          Este es un mensaje automático generado por {$siteName}. Por favor no respondas a este correo.{$footerContact}
        </td></tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

        return $this->to($this->incomingMail->from)
            ->subject("Tu solicitud ha sido procesada — {$siteName}")
            ->html($html);
    }
}
