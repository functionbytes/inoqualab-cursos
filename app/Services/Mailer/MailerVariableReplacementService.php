<?php

namespace App\Services\Mailer;

use App\Models\Mailer\MailerTemplate;

class MailerVariableReplacementService
{
    public static function getPreviewVariablesForTemplate(MailerTemplate $template): array
    {
        $vars = $template->getAvailableVariables();
        $variables = [];

        foreach ($vars as $var) {
            $variables[$var['name']] = MailerTemplateRendererService::getExampleValue($var['name']);
        }

        return array_merge(self::getBaseVariables([]), $variables);
    }

    public static function getBaseVariables(array $realValues = []): array
    {
        $appName = config('app.name', 'Plataforma');
        $appUrl = rtrim(config('app.url', 'https://example.com'), '/');
        $appHost = parse_url($appUrl, PHP_URL_HOST) ?: 'example.com';

        return array_merge([
            'SITE_NAME' => $appName,
            'SITE_URL' => $appUrl,
            'SUPPORT_EMAIL' => config('mail.from.address', "noreply@{$appHost}"),
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_DATE' => date('d/m/Y'),
        ], $realValues);
    }

    public static function replaceVariables(string $content, array $variables): string
    {
        return MailerTemplateRendererService::replaceVariables($content, $variables);
    }

    public static function getUnreplacedVariables(string $content): array
    {
        preg_match_all('/\{([A-Z_]+)\}/', $content, $matches);

        return array_unique($matches[1]);
    }
}
