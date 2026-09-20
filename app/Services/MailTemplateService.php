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
            'html' => $this->wrap($this->replace($template->content, $vars), $vars),
        ];
    }

    /**
     * Renders raw HTML content (not from DB) with global variables replaced.
     * Template-specific vars that are not provided remain as {VAR_NAME}.
     * Used for plain text-ish fragments (e.g. the subject line) that must NOT
     * get the letterhead/footer shell -- for the email body use renderRawBody().
     */
    public function renderRaw(string $content, array $variables = []): string
    {
        $vars = array_merge($this->globalVariables(), array_change_key_case($variables, CASE_UPPER));

        return $this->replace($content, $vars);
    }

    /**
     * Same as renderRaw() but wraps the result in the shared letterhead/footer
     * shell, matching what render() produces. Used by the admin panel's live
     * preview and "enviar prueba" so what the editor sees while typing the
     * inner fragment matches the real email a customer would receive.
     */
    public function renderRawBody(string $content, array $variables = []): string
    {
        $vars = array_merge($this->globalVariables(), array_change_key_case($variables, CASE_UPPER));

        return $this->wrap($this->replace($content, $vars), $vars);
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

    /**
     * Envuelve un fragmento de contenido (ya con variables reemplazadas) en el
     * cascarón de correo compartido: logo fuera de la tarjeta sobre fondo gris,
     * tarjeta blanca redondeada con barra superior de acento, copyright debajo.
     * `content` en la BD solo guarda este fragmento interno -- nunca el <html>
     * completo -- para que todas las plantillas compartan el mismo letterhead
     * y cambiarlo no implique editar 16 filas una por una.
     */
    private function wrap(string $innerHtml, array $vars): string
    {
        $siteName = $vars['SITE_NAME'] ?? config('app.name');
        $siteUrl = $vars['SITE_URL'] ?? rtrim(getUrl(), '/');
        $logoUrl = $vars['LOGO_URL'] ?? getLogo();
        $year = $vars['CURRENT_YEAR'] ?? date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$siteName}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cal+Sans&family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
</head>
<body style="margin:0;padding:0;background-color:#F1F4F6;font-family:'Figtree',Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:0;padding:0;width:100%;background-color:#F1F4F6;">
<tr>
<td align="center" valign="top" style="margin:0;padding:24px 12px;background-color:#F1F4F6;">

<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width:600px;max-width:600px;margin:0 0 28px;">
<tr>
<td align="center" style="padding:8px 24px 0;">
<a href="{$siteUrl}" target="_blank" style="text-decoration:none;border:0;">
<img src="{$logoUrl}" alt="{$siteName}" width="180" style="display:block;border:0;outline:none;text-decoration:none;width:180px;max-width:180px;height:auto;margin:0 auto;">
</a>
</td>
</tr>
</table>

<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width:600px;max-width:600px;background-color:#FFFFFF;border:1px solid #e6eaee;border-radius:14px;overflow:hidden;">
<tr>
<td style="background-color:#008bce;height:3px;line-height:3px;font-size:0;">&nbsp;</td>
</tr>
<tr>
<td align="left" style="padding:40px 44px 36px;background-color:#FFFFFF;font-family:'Figtree',Arial,Helvetica,sans-serif;">
{$innerHtml}
</td>
</tr>
</table>

<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" align="center" style="width:600px;max-width:600px;">
<tr>
<td align="center" style="padding:18px 40px 0;font-family:'Figtree',Arial,Helvetica,sans-serif;">
<p style="margin:0;font-size:11px;color:#8a95a1;text-align:center;line-height:1.6;">
&copy; {$year} {$siteName}. Todos los derechos reservados. &middot;
<a href="{$siteUrl}" style="color:#8a95a1;text-decoration:underline;">{$siteUrl}</a>
</p>
</td>
</tr>
</table>

</td>
</tr>
</table>
</body>
</html>
HTML;
    }
}
