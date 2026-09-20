<?php

namespace App\Mail\Newsletter;

use App\Models\Mailer\MailerTemplate;
use App\Models\Newsletter;
use App\Services\Mailer\MailerTemplateRendererService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewSubscriberAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Newsletter $newsletter
    ) {}

    public function build(): self
    {
        $to = setting('newsletter_notification_email') ?: config('mail.from.address');

        $template = MailerTemplate::where('key', 'newsletter.admin_notification')
            ->where('is_enabled', true)
            ->with('layout')
            ->first();

        $variables = $this->variables();

        if ($template) {
            $subject = MailerTemplateRendererService::replaceVariables($template->subject, $variables);
            $html = MailerTemplateRendererService::renderEmailTemplate($template, $variables);
        } else {
            $subject = 'Nuevo suscriptor al newsletter — '.config('app.name');
            $name = $this->newsletter->name ? ' ('.$this->newsletter->name.')' : '';
            $html = '<p>Nuevo suscriptor: <strong>'.$this->newsletter->email.'</strong>'.$name.'</p>'
                .'<p>Fecha: '.$this->newsletter->created_at?->format('d/m/Y H:i').'</p>';
        }

        return $this->to($to)
            ->subject($subject)
            ->html($html);
    }

    private function variables(): array
    {
        $sourceLabel = match ($this->newsletter->source) {
            'registration' => 'Registro en plataforma',
            'manual' => 'Añadido manualmente',
            'import' => 'Importación CSV',
            default => 'Formulario web',
        };

        return [
            'SITE_NAME' => config('app.name'),
            'SITE_URL' => config('app.url'),
            'SITE_LOGO_URL' => getlogo(),
            'SUPPORT_EMAIL' => config('mail.from.address'),
            'CURRENT_YEAR' => date('Y'),
            'SUBSCRIBER_EMAIL' => $this->newsletter->email,
            'SUBSCRIBER_NAME' => $this->newsletter->name ?? '—',
            'SUBSCRIBER_SOURCE' => $sourceLabel,
            'SUBSCRIBER_DATE' => $this->newsletter->created_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
        ];
    }
}
