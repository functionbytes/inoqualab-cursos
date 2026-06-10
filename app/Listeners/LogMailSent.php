<?php

namespace App\Listeners;

use App\Models\MailLog;
use App\Models\User;
use Illuminate\Mail\Events\MessageSent;
use Symfony\Component\Mime\Email;
use Throwable;

class LogMailSent
{
    public function handle(MessageSent $event): void
    {
        try {
            // Laravel 10 passes the original Symfony Email in $event->data['message']
            $email = $event->data['message'] ?? null;

            // Fallback: unwrap via SentMessage::getOriginalMessage()
            if (! $email instanceof Email) {
                $raw = method_exists($event->message, 'getOriginalMessage')
                    ? $event->message->getOriginalMessage()
                    : null;
                if ($raw instanceof Email) {
                    $email = $raw;
                }
            }

            if (! $email instanceof Email) {
                return;
            }

            $subject = $email->getSubject() ?? '';
            $htmlBody = $email->getHtmlBody() ?? '';

            // Skip test emails sent from the template editor
            if (str_starts_with($subject, '[PRUEBA]')) {
                return;
            }

            foreach ($email->getTo() as $address) {
                $recipientEmail = $address->getAddress();
                $user = User::where('email', $recipientEmail)->first();

                MailLog::create([
                    'user_id' => $user?->id,
                    'recipient_email' => $recipientEmail,
                    'subject' => $subject,
                    'body_html' => $htmlBody,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }
        } catch (Throwable) {
            // Never let logging break the mail send flow
        }
    }
}
