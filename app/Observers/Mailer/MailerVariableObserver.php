<?php

namespace App\Observers\Mailer;

use App\Models\Mailer\MailerVariable;
use App\Services\Mailer\MailerTemplateRendererService;

class MailerVariableObserver
{
    public function created(MailerVariable $variable): void
    {
        MailerTemplateRendererService::clearCache();
    }

    public function updated(MailerVariable $variable): void
    {
        MailerTemplateRendererService::clearCache();
    }

    public function deleted(MailerVariable $variable): void
    {
        MailerTemplateRendererService::clearCache();
    }
}
