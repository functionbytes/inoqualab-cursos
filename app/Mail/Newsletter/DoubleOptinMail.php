<?php

namespace App\Mail\Newsletter;

use App\Models\Mailer\MailerTemplate;
use App\Models\Newsletter;
use App\Services\Mailer\MailerTemplateRendererService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DoubleOptinMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly Newsletter $newsletter
    ) {}

    public function build(): self
    {
        $template = MailerTemplate::where('key', 'newsletter.double_optin')
            ->where('is_enabled', true)
            ->with('layout')
            ->first();

        $variables = $this->variables();

        if ($template) {
            $subject = MailerTemplateRendererService::replaceVariables($template->subject, $variables);
            $html = MailerTemplateRendererService::renderEmailTemplate($template, $variables);
        } else {
            $subject = 'Confirma tu suscripción a '.config('app.name');
            $html = '<p>Haz clic en el enlace para confirmar tu suscripción: '
                .'<a href="'.$variables['CONFIRM_URL'].'">Confirmar suscripción</a></p>';
        }

        return $this->to($this->newsletter->email)
            ->subject($subject)
            ->html($html);
    }

    private function variables(): array
    {
        $name = $this->newsletter->name ? ' '.$this->newsletter->name : '';

        return [
            'SITE_NAME' => config('app.name'),
            'SITE_URL' => config('app.url'),
            'SITE_LOGO_URL' => getlogo(),
            'SUPPORT_EMAIL' => config('mail.from.address'),
            'CURRENT_YEAR' => date('Y'),
            'SUBSCRIBER_EMAIL' => $this->newsletter->email,
            'SUBSCRIBER_NAME' => $name,
            'CONFIRM_URL' => route('newsletters.confirm', $this->newsletter->confirmation_token),
        ];
    }
}
