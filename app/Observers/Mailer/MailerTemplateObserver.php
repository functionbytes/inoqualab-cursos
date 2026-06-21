<?php

namespace App\Observers\Mailer;

use App\Models\Mailer\MailerTemplate;
use App\Services\Mailer\MailerTemplateRendererService;

class MailerTemplateObserver
{
    public function created(MailerTemplate $template): void
    {
        MailerTemplateRendererService::clearCache();
    }

    public function updated(MailerTemplate $template): void
    {
        MailerTemplateRendererService::clearCache();
    }

    public function deleted(MailerTemplate $template): void
    {
        MailerTemplateRendererService::clearCache();
    }
}
