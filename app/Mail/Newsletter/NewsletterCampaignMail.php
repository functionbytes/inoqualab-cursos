<?php

namespace App\Mail\Newsletter;

use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use App\Services\Mailer\MailerTemplateRendererService;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterCampaignMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        private readonly NewsletterCampaign $campaign,
        private readonly Newsletter $subscriber
    ) {}

    public function build(): self
    {
        $variables = [
            'SITE_NAME' => config('app.name'),
            'SITE_URL' => config('app.url'),
            'SITE_LOGO_URL' => getlogo(),
            'SUPPORT_EMAIL' => config('mail.from.address'),
            'CURRENT_YEAR' => date('Y'),
            'SUBSCRIBER_EMAIL' => $this->subscriber->email,
            'SUBSCRIBER_NAME' => $this->subscriber->name ? ' '.$this->subscriber->name : '',
            'UNSUBSCRIBE_URL' => route('newsletters.unsubscribe', $this->subscriber->slack),
        ];

        $subject = MailerTemplateRendererService::replaceVariables($this->campaign->subject, $variables);
        $html = MailerTemplateRendererService::renderContentWithWrapper($this->campaign->content, $variables);

        return $this->to($this->subscriber->email)
            ->subject($subject)
            ->html($html);
    }
}
