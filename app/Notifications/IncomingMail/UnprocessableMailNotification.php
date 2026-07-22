<?php

namespace App\Notifications\IncomingMail;

use App\Models\Mail\IncomingMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UnprocessableMailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IncomingMail $mail
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'incoming_mail_failed',
            'title' => 'Correo no procesable',
            'message' => "El correo \"{$this->mail->subject}\" de {$this->mail->from} no pudo procesarse: {$this->mail->error_log}",
            'entity_id' => $this->mail->id,
            // Enlaza a la bandeja de correos entrantes (destinatarios: support/manager).
            'action_url' => route('support.mails.show', $this->mail->slack),
        ];
    }
}
