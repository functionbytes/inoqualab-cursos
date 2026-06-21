<?php

namespace App\Observers\Mailer;

use App\Models\Mailer\MailerLayout;
use App\Services\Mailer\MailerTemplateRendererService;

class MailerLayoutObserver
{
    public function created(MailerLayout $layout): void
    {
        MailerTemplateRendererService::clearCache();
    }

    public function updated(MailerLayout $layout): void
    {
        MailerTemplateRendererService::clearCache();
    }

    public function deleted(MailerLayout $layout): void
    {
        MailerTemplateRendererService::clearCache();
    }
}
