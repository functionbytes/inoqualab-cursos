<?php

namespace App\Services\Mailer;

use App\Models\Mailer\MailerLayout;
use App\Models\Mailer\MailerTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Twig\Environment;
use Twig\Extension\SandboxExtension;
use Twig\Loader\ArrayLoader;
use Twig\Sandbox\SecurityPolicy;

class MailerTemplateRendererService
{
    private static ?Environment $twigEnv = null;

    private static function getTwigEnvironment(): Environment
    {
        if (self::$twigEnv === null) {
            $loader = new ArrayLoader;
            self::$twigEnv = new Environment($loader, [
                'autoescape' => false,
                'strict_variables' => false,
            ]);

            $policy = new SecurityPolicy(
                allowedTags: ['if', 'for', 'set', 'block'],
                allowedFilters: [
                    'upper', 'lower', 'title', 'capitalize', 'trim',
                    'escape', 'e', 'raw',
                    'length', 'default', 'replace', 'split', 'join',
                    'date', 'date_modify',
                    'number_format', 'abs', 'round',
                    'url_encode', 'json_encode',
                    'striptags', 'nl2br',
                ],
                allowedMethods: [],
                allowedProperties: [],
                allowedFunctions: ['range', 'max', 'min', 'random']
            );

            self::$twigEnv->addExtension(new SandboxExtension($policy, true));
        }

        return self::$twigEnv;
    }

    public static function renderEmailTemplate(MailerTemplate $template, array $variables = []): string
    {
        $content = $template->content ?? '';

        // Replace variables {TAG}
        $content = self::replaceVariables($content, $variables);

        // Apply layout if set
        if ($template->layout) {
            $layoutContent = $template->layout->content ?? '';
            $layoutContent = self::replaceVariables($layoutContent, $variables);
            $layoutContent = str_replace('{{ content }}', $content, $layoutContent);
            $layoutContent = self::renderLayoutTags($layoutContent, $variables);
            $content = $layoutContent;
        }

        return $content;
    }

    public static function renderContentWithWrapper(string $htmlContent, array $variables = []): string
    {
        $content = self::replaceVariables($htmlContent, $variables);

        $wrapperContent = self::getCachedLayoutContent('email_template_wrapper');

        if (! $wrapperContent) {
            return $content;
        }

        $wrapperContent = self::replaceVariables($wrapperContent, $variables);
        $wrapperContent = str_replace('{{ content }}', $content, $wrapperContent);
        $wrapperContent = self::renderLayoutTags($wrapperContent, $variables);

        return $wrapperContent;
    }

    private static function renderLayoutTags(string $content, array $variables = []): string
    {
        if (str_contains($content, '{{ header }}')) {
            $headerContent = self::getCachedLayoutContent('email_template_header');
            if ($headerContent) {
                $headerContent = self::replaceVariables($headerContent, $variables);
                $content = str_replace('{{ header }}', $headerContent, $content);
            } else {
                $content = str_replace('{{ header }}', '', $content);
            }
        }

        if (str_contains($content, '{{ footer }}')) {
            $footerContent = self::getCachedLayoutContent('email_template_footer');
            if ($footerContent) {
                $footerContent = self::replaceVariables($footerContent, $variables);
                $content = str_replace('{{ footer }}', $footerContent, $content);
            } else {
                $content = str_replace('{{ footer }}', '', $content);
            }
        }

        return $content;
    }

    private static function getCachedLayoutContent(string $alias): ?string
    {
        static $staticCache = [];

        $cacheKey = "mailer_layout_{$alias}";

        if (isset($staticCache[$cacheKey])) {
            return $staticCache[$cacheKey];
        }

        $content = Cache::get($cacheKey);

        if ($content === null) {
            $layout = MailerLayout::where('alias', $alias)->where('is_enabled', true)->first();
            if ($layout) {
                $content = $layout->content;
                if ($content !== null) {
                    Cache::put($cacheKey, $content, 3600);
                }
            }
        }

        $staticCache[$cacheKey] = $content;

        return $content;
    }

    public static function clearCache(): void
    {
        $aliases = config('mailer-module.cache_layout_aliases', [
            'email_template_header',
            'email_template_footer',
            'email_template_wrapper',
        ]);

        foreach ($aliases as $alias) {
            Cache::forget("mailer_layout_{$alias}");
        }
    }

    public static function replaceVariables(string $content, array $variables = []): string
    {
        if (self::usesTwigSyntax($content)) {
            return self::renderWithTwig($content, $variables);
        }

        foreach ($variables as $key => $value) {
            $placeholder = str_starts_with($key, '{') ? $key : '{'.$key.'}';
            if (! is_array($value) && ! is_object($value)) {
                $content = str_replace($placeholder, (string) $value, $content);
            }
        }

        return $content;
    }

    private static function usesTwigSyntax(string $content): bool
    {
        return preg_match('/\{%\s*.+?\s*%\}/', $content) > 0;
    }

    private static function renderWithTwig(string $content, array $variables = []): string
    {
        try {
            $content = preg_replace_callback(
                '/\{([A-Z_][A-Z0-9_]*)\}/',
                fn ($matches) => '{{ '.$matches[1].' }}',
                $content
            );

            $twig = self::getTwigEnvironment();
            $twig->getLoader()->setTemplate('template', $content);

            $normalizedVars = [];
            foreach ($variables as $key => $value) {
                $cleanKey = str_replace(['{', '}'], '', $key);
                $normalizedVars[$cleanKey] = $value;
            }

            return $twig->render('template', $normalizedVars);
        } catch (\Exception $e) {
            Log::error('MailerTemplateRenderer: Twig error', [
                'error' => $e->getMessage(),
            ]);

            // Fallback a reemplazo simple
            foreach ($variables as $key => $value) {
                $placeholder = str_starts_with($key, '{') ? $key : '{'.$key.'}';
                if (! is_array($value) && ! is_object($value)) {
                    $content = str_replace($placeholder, (string) $value, $content);
                }
            }

            return $content;
        }
    }

    public static function getPreviewHtml(MailerTemplate $template): string
    {
        $variables = [];
        foreach ($template->getAvailableVariables() as $var) {
            $variables[$var['name']] = self::getExampleValue($var['name']);
        }

        return self::renderEmailTemplate($template, $variables);
    }

    public static function getExampleValue(string $variableName): string
    {
        $appName = (string) config('app.name', 'Plataforma');
        $appUrl = rtrim((string) config('app.url', 'https://example.com'), '/');
        $appHost = parse_url($appUrl, PHP_URL_HOST) ?: 'example.com';
        $fromAddress = (string) config('mail.from.address', "noreply@{$appHost}");

        $examples = [
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DATE' => date('d/m/Y'),
            'USER_NAME' => 'Juan García',
            'USER_EMAIL' => "juan@{$appHost}",
            'USER_FIRST_NAME' => 'Juan',
            'USER_LAST_NAME' => 'García',
            'USER_PHONE' => '+52 555 000 0000',
            'CUSTOMER_NAME' => 'Juan García',
            'CUSTOMER_EMAIL' => "juan@{$appHost}",
            'ORDER_ID' => '12345',
            'ORDER_NUMBER' => 'ORD-2025-001',
            'ORDER_DATE' => date('d/m/Y'),
            'ORDER_TOTAL' => '$5,000.00',
            'ORDER_STATUS' => 'Completada',
            'SITE_NAME' => $appName,
            'SITE_URL' => $appUrl,
            'SITE_LOGO_URL' => getlogo(),
            'SUPPORT_EMAIL' => $fromAddress,
            'SUPPORT_PHONE' => '+52 555 000 0000',
            'COMPANY_NAME' => $appName,
            'COMPANY_ADDRESS' => 'Calle Principal 123',
            'COMPANY_CITY' => 'Ciudad de México',
            'COMPANY_COUNTRY' => 'México',
            'LOGIN_URL' => "{$appUrl}/login",
            'RESET_PASSWORD_URL' => "{$appUrl}/password/reset/abc123",
            'VERIFY_EMAIL_URL' => "{$appUrl}/email/verify/abc123",
            'ACCOUNT_DASHBOARD_URL' => "{$appUrl}/dashboard",
            'UNSUBSCRIBE_URL' => "{$appUrl}/unsubscribe/abc123",
        ];

        return $examples[$variableName] ?? '{{'.$variableName.'}}';
    }

    public static function htmlToPlainText(string $html): string
    {
        $text = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
        $text = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $text);
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/<p[^>]*>/i', "\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text);

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    public static function beautifyHtml(string $html): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $formatted = $dom->saveHTML();
        $formatted = str_replace('<?xml encoding="UTF-8">', '', $formatted);

        return trim($formatted);
    }
}
