<?php

namespace App\Services;

use App\Models\MailTemplate;

class MailTemplateService
{
    /**
     * Renders a template by key, replacing all {VAR} placeholders.
     * Returns ['subject' => '...', 'html' => '...'].
     * Falls back to ['subject' => $key, 'html' => ''] when the key is not found.
     */
    public function render(string $key, array $variables = []): array
    {
        $template = MailTemplate::resolve($key);

        if (! $template) {
            return ['subject' => $key, 'html' => ''];
        }

        $vars = array_merge($this->globalVariables(), array_change_key_case($variables, CASE_UPPER));

        return [
            'subject' => $this->replace($template->subject, $vars),
            'html' => $this->replace($template->content, $vars),
        ];
    }

    /**
     * Renders raw HTML content (not from DB) with global variables replaced.
     * Template-specific vars that are not provided remain as {VAR_NAME}.
     */
    public function renderRaw(string $content, array $variables = []): string
    {
        $vars = array_merge($this->globalVariables(), array_change_key_case($variables, CASE_UPPER));

        return $this->replace($content, $vars);
    }

    /**
     * Site-wide variables auto-injected in every render.
     */
    private function globalVariables(): array
    {
        // settings es una tabla key/value: usar el helper setting($clave). NO
        // getSetting()->page_email (getSetting() no acepta args y devuelve el
        // modelo Setting completo, así que esas "columnas" siempre eran null).
        return [
            'SITE_NAME' => setting('page_title') ?: config('app.name'),
            'SITE_URL' => rtrim(getUrl(), '/'),
            'LOGO_URL' => getLogo(),
            'SUPPORT_EMAIL' => setting('page_email') ?: '',
            'SUPPORT_PHONE' => setting('page_phone') ?: '',
            'CURRENT_YEAR' => date('Y'),
        ];
    }

    private function replace(string $text, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $text = str_replace('{'.strtoupper($key).'}', $value ?? '', $text);
        }

        return $text;
    }
}
