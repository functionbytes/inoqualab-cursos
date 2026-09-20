<?php

namespace App\Mail\Newsletter;

use App\Models\Mailer\MailerTemplate;
use App\Models\Newsletter;
use App\Services\Mailer\MailerTemplateRendererService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscribedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly Newsletter $newsletter
    ) {}

    public function build(): self
    {
        $template = MailerTemplate::where('key', 'newsletter.subscribed')
            ->where('is_enabled', true)
            ->with('layout')
            ->first();

        $variables = $this->variables();

        if ($template) {
            $subject = MailerTemplateRendererService::replaceVariables($template->subject, $variables);
            $html = MailerTemplateRendererService::renderEmailTemplate($template, $variables);
        } else {
            $subject = '¡Gracias por suscribirte!';
            $html = $this->fallbackHtml();
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
            // getlogo() resuelve el logo real (Media Library, setting page_logo);
            // '/images/logo.png' no existe en public/ -- el logo salía roto en el correo.
            'SITE_LOGO_URL' => getlogo(),
            'SUPPORT_EMAIL' => config('mail.from.address'),
            'CURRENT_YEAR' => date('Y'),
            'SUBSCRIBER_EMAIL' => $this->newsletter->email,
            'SUBSCRIBER_NAME' => $name,
            'UNSUBSCRIBE_URL' => route('newsletters.unsubscribe', $this->newsletter->slack),
        ];
    }

    private function fallbackHtml(): string
    {
        return '<p>Te has suscrito correctamente al newsletter de '.e(config('app.name')).'.</p>'
            .'<p>Para darte de baja, <a href="'.route('newsletters.unsubscribe', $this->newsletter->slack).'">haz clic aquí</a>.</p>';
    }
}
