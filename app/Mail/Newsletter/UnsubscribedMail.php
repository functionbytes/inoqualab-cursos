<?php

namespace App\Mail\Newsletter;

use App\Models\Mailer\MailerTemplate;
use App\Services\Mailer\MailerTemplateRendererService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UnsubscribedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $email
    ) {}

    public function build(): self
    {
        $template = MailerTemplate::where('key', 'newsletter.unsubscribed')
            ->where('is_enabled', true)
            ->with('layout')
            ->first();

        $variables = [
            'SITE_NAME' => config('app.name'),
            'SITE_URL' => config('app.url'),
            'SITE_LOGO_URL' => config('app.url').'/images/logo.png',
            'SUPPORT_EMAIL' => config('mail.from.address'),
            'CURRENT_YEAR' => date('Y'),
            'SUBSCRIBER_EMAIL' => $this->email,
        ];

        if ($template) {
            $subject = MailerTemplateRendererService::replaceVariables($template->subject, $variables);
            $html = MailerTemplateRendererService::renderEmailTemplate($template, $variables);
        } else {
            $subject = 'Te has dado de baja del newsletter';
            $html = '<p>El correo '.e($this->email).' ha sido eliminado de nuestra lista de newsletter.</p>';
        }

        return $this->to($this->email)
            ->subject($subject)
            ->html($html);
    }
}
