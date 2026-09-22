<?php

namespace App\Html;

/**
 * Set de iconos SVG propios para el rail de navegación (side-mini-panel),
 * inspirado en modules/Theme/app/Helpers/NavIconHelper.php de webadmin.
 *
 * Estilo "trazo cercano": contorno redondeado de 1.9px (linecap/linejoin
 * round) y relleno plano. No dependen de Font Awesome ni de ningún icon
 * font — el mismo SVG sirve para el estado inactivo y el activo cambiando
 * solo las variables CSS `--icon-stroke` (contorno) e `--icon-fill`
 * (relleno), definidas en public/managers/css/style.css.
 *
 * Excepción documentada a la regla "Font Awesome 6 ONLY" de
 * .claude/rules/blade-views.md: aplica solo al rail de iconos
 * (side-mini-panel .mini-nav-item), no al resto de iconografía del panel.
 */
class NavIconHelper
{
    public static function render(string $key): string
    {
        $inner = self::paths()[$key] ?? self::paths()['dot'];

        return '<svg viewBox="0 0 24 24" fill="none" stroke="var(--icon-stroke, #000000)" stroke-width="1.9" '
            .'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$inner.'</svg>';
    }

    /**
     * @return array<string, string>
     */
    private static function paths(): array
    {
        return [
            'home' => '
                <path d="M3.5 11.2 12 4l8.5 7.2" />
                <path d="M5.3 10v8.3a1.4 1.4 0 0 0 1.4 1.4h10.6a1.4 1.4 0 0 0 1.4-1.4V10" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M9.8 19.6v-5a1 1 0 0 1 1-1h2.4a1 1 0 0 1 1 1v5" fill="none" />
            ',

            'courses' => '
                <path d="M3 9.3 12 5l9 4.3-9 4.3-9-4.3Z" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M7 11.4v4c0 1.4 2.2 2.6 5 2.6s5-1.2 5-2.6v-4" fill="none" />
                <path d="M20 9.3v5" />
            ',

            'platform' => '
                <path d="M12 3.6 21 8.2 12 12.8 3 8.2Z" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M3 12.4 12 17l9-4.6" fill="none" />
                <path d="M3 16.4 12 21l9-4.6" fill="none" />
            ',

            'wallet' => '
                <rect x="3" y="6.5" width="14" height="12" rx="3" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M3 10.5h14" />
                <circle cx="18" cy="7" r="3.6" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M18 5.3v3.4M16.3 7h3.4" />
            ',

            'mail' => '
                <rect x="3" y="5.8" width="18" height="12.4" rx="4" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M4.2 7.3 12 13 19.8 7.3" fill="none" />
            ',

            'newsletter' => '
                <path d="M21 3.5 3 10.8l6.7 2.6L21 3.5Z" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M21 3.5 13.6 20.5l-3.9-7.1" fill="none" />
                <path d="M9.7 13.4 21 3.5" fill="none" />
            ',

            'reviews' => '
                <path d="M12 3.6 14.6 9 20.6 9.9 16.3 14 17.3 20 12 17.1 6.7 20 7.7 14 3.4 9.9 9.4 9Z" fill="var(--icon-fill, #e4e4e7)" />
            ',

            'users' => '
                <circle cx="9.2" cy="8.3" r="3" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M3.8 19c.3-3.4 2.6-5.6 5.4-5.6s5.1 2.2 5.4 5.6" fill="none" />
                <circle cx="16.8" cy="9.4" r="2.4" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M14.8 19c.2-2.6 1.8-4.3 4-4.3" fill="none" />
            ',

            'help' => '
                <circle cx="12" cy="12" r="8.4" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M9.6 9.7a2.4 2.4 0 1 1 3.4 2.2c-.8.4-1 .8-1 1.6" fill="none" />
                <circle cx="12" cy="16.6" r="1.1" fill="var(--icon-stroke, #000000)" stroke="none" />
            ',

            'chart' => '
                <path d="M3.5 20h17" />
                <rect x="4.3" y="12.3" width="3.2" height="7.2" rx=".8" fill="var(--icon-fill, #e4e4e7)" />
                <rect x="10.4" y="7" width="3.2" height="12.5" rx=".8" fill="var(--icon-fill, #e4e4e7)" />
                <rect x="16.5" y="10" width="3.2" height="9.5" rx=".8" fill="var(--icon-fill, #e4e4e7)" />
            ',

            'sliders' => '
                <path d="M4.5 7h9.5M17.5 7h2M4.5 12h4.5M12 12h7.5M4.5 17h7.5M15 17h4.5" />
                <circle cx="16" cy="7" r="2.3" fill="var(--icon-fill, #e4e4e7)" />
                <circle cx="10.5" cy="12" r="2.3" fill="var(--icon-fill, #e4e4e7)" />
                <circle cx="13" cy="17" r="2.3" fill="var(--icon-fill, #e4e4e7)" />
            ',

            'building' => '
                <rect x="5" y="3.5" width="10" height="17" rx="1.5" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M15 9.5h4.5v11H15" fill="none" />
                <path d="M8 7.5h1.2M11.8 7.5H13M8 11h1.2M11.8 11H13M8 14.5h1.2M11.8 14.5H13" />
            ',

            'people-arrows' => '
                <circle cx="7.6" cy="7.4" r="2.6" fill="var(--icon-fill, #e4e4e7)" />
                <circle cx="16.4" cy="7.4" r="2.6" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M3.6 19c.3-3 2.1-4.8 4-4.8s3.4 1.5 3.8 3.6M12.6 17.8c.4-2.1 1.9-3.6 3.8-3.6s3.7 1.8 4 4.8" fill="none" />
                <path d="M9 13.2h6M13.4 11l1.8 2.2-1.8 2.2" />
            ',

            'user-plus' => '
                <circle cx="10" cy="8.2" r="3.4" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M3.8 19.4c.4-3.7 2.9-6 6.2-6s5.8 2.3 6.2 6" fill="none" />
                <path d="M18.4 8.6v5M15.9 11.1h5" />
            ',

            'list-check' => '
                <rect x="3.4" y="4.5" width="17.2" height="15" rx="2.4" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M7.6 9.3l1.6 1.6 3-3.2M7.6 15.7l1.6 1.6 3-3.2" fill="none" />
                <path d="M14.6 8.6h4M14.6 15h4" />
            ',

            'invoice' => '
                <path d="M6 3.5h9l4 4v13H6Z" fill="var(--icon-fill, #e4e4e7)" />
                <path d="M15 3.5v4h4" fill="none" />
                <path d="M8.6 11h6.8M8.6 14h6.8M8.6 17h4.4" />
            ',

            'inbox' => '
                <path d="M4.5 12.6 6.1 6.4A2.2 2.2 0 0 1 8.2 4.8h7.6a2.2 2.2 0 0 1 2.1 1.6l1.6 6.2" fill="none" />
                <path d="M4.5 12.6h4.9l1.4 2.2h2.4l1.4-2.2h4.9v4.6a2.3 2.3 0 0 1-2.3 2.3H6.8a2.3 2.3 0 0 1-2.3-2.3v-4.6Z" fill="var(--icon-fill, #e4e4e7)" />
            ',

            'chat' => '
                <path d="M7.5 6.5h9a3 3 0 0 1 3 3v3.5a3 3 0 0 1-3 3h-4l-3.2 2.8v-2.8h-1.8a3 3 0 0 1-3-3V9.5a3 3 0 0 1 3-3Z" fill="var(--icon-fill, #e4e4e7)" />
                <circle cx="9.5" cy="12" r="1" fill="var(--icon-stroke, #000000)" stroke="none" />
                <circle cx="13" cy="12" r="1" fill="var(--icon-stroke, #000000)" stroke="none" />
                <circle cx="16.5" cy="12" r="1" fill="var(--icon-stroke, #000000)" stroke="none" />
            ',

            'folder' => '
                <path d="M3 7.2a1.6 1.6 0 0 1 1.6-1.6h4.6l2 2.2h8.2A1.6 1.6 0 0 1 21 9.4v8.2A1.6 1.6 0 0 1 19.4 19H4.6A1.6 1.6 0 0 1 3 17.4Z" fill="var(--icon-fill, #e4e4e7)" />
            ',

            'dot' => '
                <circle cx="12" cy="12" r="7" fill="var(--icon-fill, #e4e4e7)" />
                <circle cx="12" cy="12" r="2.4" fill="var(--icon-stroke, #000000)" stroke="none" />
            ',
        ];
    }
}
