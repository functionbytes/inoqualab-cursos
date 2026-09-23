<?php

namespace App\Html;

/**
 * Set de iconos SVG propios para UI general (botones, toolbars, etc.),
 * mismo estilo "trazo cercano" que App\Html\NavIconHelper (contorno
 * redondeado 1.9px) pero monocromo con currentColor — pensado para iconos
 * chicos dentro de botones, no para el rail (que necesita el esquema
 * stroke/fill de dos tonos para sus estados activo/hover).
 *
 * Excepción documentada a la regla "Font Awesome 6 ONLY" de
 * .claude/rules/blade-views.md, igual que NavIconHelper.
 */
class IconHelper
{
    public static function render(string $key, int $size = 16): string
    {
        $inner = self::paths()[$key] ?? self::paths()['dot'];

        return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
            .'stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$inner.'</svg>';
    }

    /**
     * @return array<string, string>
     */
    private static function paths(): array
    {
        return [
            'search' => '
                <circle cx="10.3" cy="10.3" r="6.3" />
                <path d="M19.5 19.5 15 15" />
            ',

            'sliders' => '
                <path d="M4.5 7h9.5M17.5 7h2M4.5 12h4.5M12 12h7.5M4.5 17h7.5M15 17h4.5" />
                <circle cx="16" cy="7" r="2" fill="currentColor" stroke="none" />
                <circle cx="10.5" cy="12" r="2" fill="currentColor" stroke="none" />
                <circle cx="13" cy="17" r="2" fill="currentColor" stroke="none" />
            ',

            'plus' => '
                <path d="M12 5v14M5 12h14" />
            ',

            'x' => '
                <path d="M6 6l12 12M18 6 6 18" />
            ',

            'bell' => '
                <path d="M12 2.6v1.5" />
                <path d="M12 4.1c-3.3 0-5.6 2.6-5.6 6.2 0 4.7-1.8 6.3-1.8 6.3h14.8s-1.8-1.6-1.8-6.3c0-3.6-2.3-6.2-5.6-6.2Z" fill="currentColor" fill-opacity=".12" />
                <path d="M10 19.4a2 2 0 0 0 4 0" />
            ',

            'bell-slash' => '
                <path d="M12 4.1c-3.3 0-5.6 2.6-5.6 6.2 0 4.7-1.8 6.3-1.8 6.3h14.8s-1.8-1.6-1.8-6.3c0-.8-.1-1.5-.4-2.2" fill="none" />
                <path d="M10 19.4a2 2 0 0 0 4 0" />
                <path d="M4 3.5l16 16.5" />
            ',

            'dot' => '
                <circle cx="12" cy="12" r="7" />
            ',

            'arrow-left' => '
                <path d="M19 12H5M12 19l-7-7 7-7" />
            ',

            'list' => '
                <path d="M4 6h16M4 12h16M4 18h16" />
            ',

            'play-circle' => '
                <circle cx="12" cy="12" r="9" />
                <path d="M10 8.5l5 3.5-5 3.5v-7z" fill="currentColor" stroke="none" />
            ',

            'megaphone' => '
                <path d="M3 10.5v3a1 1 0 0 0 1 1h2l5 4v-13l-5 4H4a1 1 0 0 0-1 1z" />
                <path d="M15.5 8.5a4 4 0 0 1 0 7" />
            ',

            'check-circle' => '
                <circle cx="12" cy="12" r="9" />
                <path d="M8.5 12l2.3 2.3L15.5 9.7" />
            ',

            'file-text' => '
                <rect x="6" y="3" width="12" height="18" rx="2" />
                <path d="M9 8h6M9 12h6M9 16h3.5" />
            ',

            // Opciones de navegación de curso (managers.views.courses.courses.navegation):
            // pensados para renderizar grandes (48-64px), por eso llevan algo
            // de relleno sutil ademas del trazo, a diferencia de los iconos
            // chicos de arriba.
            'course-topics' => '
                <path d="M4.5 6a2 2 0 0 1 2-2h11a.9.9 0 0 1 .9.9V19a.9.9 0 0 1-.9.9h-11a2 2 0 0 1-2-2Z" fill="currentColor" fill-opacity=".1" />
                <path d="M4.5 6a2 2 0 0 1 2-2h11" />
                <path d="M4.5 17.9a2 2 0 0 1 2-2h11.9" />
                <path d="M8 8.4h6.5M8 11.4h6.5" />
            ',

            'course-lessons' => '
                <rect x="3.2" y="5" width="17.6" height="12.4" rx="2.2" fill="currentColor" fill-opacity=".1" />
                <rect x="3.2" y="5" width="17.6" height="12.4" rx="2.2" />
                <path d="M10.2 9.3v3.8l3.6-1.9Z" fill="currentColor" stroke-linejoin="round" />
                <path d="M8.7 20.5h6.6" />
            ',

            'course-announcements' => '
                <path d="M4 9.8v4.4a1.6 1.6 0 0 0 1.6 1.6H7l3.8 3.1V5.1L7 8.2H5.6A1.6 1.6 0 0 0 4 9.8Z" fill="currentColor" fill-opacity=".1" />
                <path d="M4 9.8v4.4a1.6 1.6 0 0 0 1.6 1.6H7l3.8 3.1V5.1L7 8.2H5.6A1.6 1.6 0 0 0 4 9.8Z" />
                <path d="M14.3 8.1a4.3 4.3 0 0 1 0 7.8" />
                <path d="M17.2 5.9a7.8 7.8 0 0 1 0 12.2" />
            ',

            'course-quiz' => '
                <rect x="4.3" y="3.3" width="15.4" height="17.4" rx="2.3" fill="currentColor" fill-opacity=".1" />
                <rect x="4.3" y="3.3" width="15.4" height="17.4" rx="2.3" />
                <path d="M9.2 8.9a2.6 2.6 0 1 1 3.5 2.5c-.9.3-1.4 1-1.4 1.9v.3" />
                <path d="M11.3 16.9h.02" stroke-width="2.6" />
            ',

            'course-exam' => '
                <path d="M6.2 3.4h8.3l4 4v12.3a1 1 0 0 1-1 1H6.2a1 1 0 0 1-1-1V4.4a1 1 0 0 1 1-1Z" fill="currentColor" fill-opacity=".1" />
                <path d="M6.2 3.4h8.3l4 4v12.3a1 1 0 0 1-1 1H6.2a1 1 0 0 1-1-1V4.4a1 1 0 0 1 1-1Z" />
                <path d="M14.5 3.4v3.1a1 1 0 0 0 1 1h3.9" />
                <path d="M8.6 13.6l2.1 2.1 4.7-4.9" />
            ',

            // Opciones de navegación de usuario (supports.views.users.users.navegation):
            // mismo criterio que las de curso arriba (trazo grande 48-64px).
            'user-settings' => '
                <circle cx="12" cy="8.3" r="3.3" fill="currentColor" fill-opacity=".1" />
                <circle cx="12" cy="8.3" r="3.3" />
                <path d="M5 20c0-3.9 3.1-6.2 7-6.2s7 2.3 7 6.2" fill="currentColor" fill-opacity=".1" />
                <path d="M5 20c0-3.9 3.1-6.2 7-6.2s7 2.3 7 6.2" />
            ',

            'user-orders' => '
                <path d="M6.5 3.5h11a1 1 0 0 1 1 1V20l-2.3-1.4L14 20l-2-1.4L10 20l-2.2-1.4L6.5 20V4.5a1 1 0 0 1 1-1Z" fill="currentColor" fill-opacity=".1" />
                <path d="M6.5 3.5h11a1 1 0 0 1 1 1V20l-2.3-1.4L14 20l-2-1.4L10 20l-2.2-1.4L6.5 20V4.5a1 1 0 0 1 1-1Z" />
                <path d="M9 8h6M9 11.3h6M9 14.6h3.5" />
            ',

            'user-inscriptions' => '
                <rect x="4" y="5" width="16" height="15" rx="2" fill="currentColor" fill-opacity=".1" />
                <rect x="4" y="5" width="16" height="15" rx="2" />
                <path d="M4 9.5h16" />
                <path d="M8 3.5v3M16 3.5v3" />
                <path d="M9 14.3l2 2 4-4.3" />
            ',

            'user-certificates' => '
                <circle cx="12" cy="9.3" r="5.8" fill="currentColor" fill-opacity=".1" />
                <circle cx="12" cy="9.3" r="5.8" />
                <path d="M9 13.6l-1.6 6 4.6-2.4 4.6 2.4-1.6-6" />
            ',

            'user-results' => '
                <rect x="3.5" y="3.5" width="17" height="17" rx="2.3" fill="currentColor" fill-opacity=".1" />
                <rect x="3.5" y="3.5" width="17" height="17" rx="2.3" />
                <path d="M7.5 15.5v2M12 11.5v6M16.5 8.5v9" />
            ',

            // Opciones de navegación de distribuidor/empresa (managers/supports/distributors
            // .views.{distributors,enterprises}.*.navegation): mismo criterio que las de
            // arriba (trazo grande 48-64px).
            'nav-enterprises' => '
                <path d="M4.5 20V6.5a1 1 0 0 1 .5-.87L11 2l6 3.63a1 1 0 0 1 .5.87V20" fill="currentColor" fill-opacity=".1" />
                <path d="M4.5 20V6.5a1 1 0 0 1 .5-.87L11 2l6 3.63a1 1 0 0 1 .5.87V20" />
                <path d="M3 20h16M9 20v-4h4v4" />
                <path d="M8.5 9.5h1M12.5 9.5h1M8.5 13h1M12.5 13h1" />
            ',

            'nav-people' => '
                <circle cx="9" cy="8" r="2.8" fill="currentColor" fill-opacity=".1" />
                <circle cx="9" cy="8" r="2.8" />
                <path d="M3.5 19c0-3.3 2.5-5.3 5.5-5.3s5.5 2 5.5 5.3" fill="currentColor" fill-opacity=".1" />
                <path d="M3.5 19c0-3.3 2.5-5.3 5.5-5.3s5.5 2 5.5 5.3" />
                <path d="M15 7.3a2.5 2.5 0 1 1 .5 4.9" />
                <path d="M17.5 13.8c2 .5 3 1.9 3 4.2" />
            ',

            'nav-courses' => '
                <path d="M4 5.3c2.3-.9 5-.9 7 .6 2-1.5 4.7-1.5 7-.6v12.4c-2.3-.9-5-.9-7 .6-2-1.5-4.7-1.5-7-.6Z" fill="currentColor" fill-opacity=".1" />
                <path d="M4 5.3c2.3-.9 5-.9 7 .6 2-1.5 4.7-1.5 7-.6v12.4c-2.3-.9-5-.9-7 .6-2-1.5-4.7-1.5-7-.6Z" />
                <path d="M11 5.9v12.4" />
            ',

            'nav-rates' => '
                <path d="M11.5 3.5h5.8a1 1 0 0 1 1 1v5.8a1 1 0 0 1-.3.7l-8 8a1 1 0 0 1-1.4 0l-6.1-6.1a1 1 0 0 1 0-1.4l8-8a1 1 0 0 1 .7-.3Z" fill="currentColor" fill-opacity=".1" />
                <path d="M11.5 3.5h5.8a1 1 0 0 1 1 1v5.8a1 1 0 0 1-.3.7l-8 8a1 1 0 0 1-1.4 0l-6.1-6.1a1 1 0 0 1 0-1.4l8-8a1 1 0 0 1 .7-.3Z" />
                <circle cx="15" cy="7.5" r="1.3" fill="currentColor" stroke="none" />
            ',

            'nav-reassign' => '
                <path d="M4 8h11.5M15.5 8l-3-3M15.5 8l-3 3" />
                <path d="M20 16H8.5M8.5 16l3-3M8.5 16l3 3" />
            ',

            'nav-registers' => '
                <rect x="3.5" y="6" width="17" height="13" rx="2" fill="currentColor" fill-opacity=".1" />
                <rect x="3.5" y="6" width="17" height="13" rx="2" />
                <path d="M3.5 13h4.2l1.3 2h5.5l1.3-2h4.2" />
                <path d="M8 6V4.5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1V6" />
            ',

            'nav-invoices' => '
                <rect x="5.5" y="3" width="13" height="18" rx="2" fill="currentColor" fill-opacity=".1" />
                <rect x="5.5" y="3" width="13" height="18" rx="2" />
                <path d="M9 8h6M9 11.3h6" />
                <circle cx="12" cy="16" r="2.3" />
                <path d="M12 14.3v.5M12 17.2v.5" />
            ',

            // Automatizaciones de remarketing (managers.views.newsletter.remarketing.index).
            'remarketing-crosssell' => '
                <path d="M4 9.3h16v9.4a1.3 1.3 0 0 1-1.3 1.3H5.3A1.3 1.3 0 0 1 4 18.7Z" fill="currentColor" fill-opacity=".1" />
                <path d="M4 9.3h16v9.4a1.3 1.3 0 0 1-1.3 1.3H5.3A1.3 1.3 0 0 1 4 18.7Z" />
                <path d="M3.3 6.8a1.3 1.3 0 0 1 1.3-1.3h14.8a1.3 1.3 0 0 1 1.3 1.3v2.5H3.3Z" fill="currentColor" fill-opacity=".18" />
                <path d="M12 5.5v14.5" />
                <path d="M12 5.5c-1.2-3.1-5.3-3-5.3.4 0 1.3 2.1 1 5.3-.4Z" />
                <path d="M12 5.5c1.2-3.1 5.3-3 5.3.4 0 1.3-2.1 1-5.3-.4Z" />
            ',

            'remarketing-certificate' => '
                <circle cx="10" cy="9.3" r="5.8" fill="currentColor" fill-opacity=".1" />
                <circle cx="10" cy="9.3" r="5.8" />
                <path d="M7.6 14 6.3 19.5l3.7-1.9 3.7 1.9L12.4 14" />
                <circle cx="17" cy="16" r="4" fill="#fff" />
                <circle cx="17" cy="16" r="4" />
                <path d="M17 14v2l1.3 1.3" />
            ',

            'remarketing-access' => '
                <path d="M6 3.3h12M6 20.7h12" />
                <path d="M7 3.3c0 3.9 2.9 5.4 5 6.5 2.1-1.1 5-2.6 5-6.5" fill="currentColor" fill-opacity=".1" />
                <path d="M7 3.3c0 3.9 2.9 5.4 5 6.5 2.1-1.1 5-2.6 5-6.5" />
                <path d="M7 20.7c0-3.9 2.9-5.4 5-6.5 2.1 1.1 5 2.6 5 6.5" fill="currentColor" fill-opacity=".1" />
                <path d="M7 20.7c0-3.9 2.9-5.4 5-6.5 2.1 1.1 5 2.6 5 6.5" />
            ',

            // Empty-states de tablas ("Sin resultados", "No hay X registrado"):
            // reemplazan los iconos Font Awesome genericos usados antes en las
            // ~90 tablas de los 6 paneles. Un icono por concepto (varios fa-*
            // sinonimos comparten el mismo empty-* aqui), mismo criterio de
            // trazo grande (40-56px) que los iconos de navegacion de arriba.
            'empty-users' => '
                <circle cx="9" cy="8" r="3" fill="currentColor" fill-opacity=".1" />
                <circle cx="9" cy="8" r="3" />
                <path d="M3.5 20c0-4 2.5-6.3 5.5-6.3s5.5 2.3 5.5 6.3" fill="currentColor" fill-opacity=".1" />
                <path d="M3.5 20c0-4 2.5-6.3 5.5-6.3s5.5 2.3 5.5 6.3" />
                <circle cx="16.5" cy="8.5" r="2.3" />
                <path d="M19 13.5c1.8.5 3 2 3 4.6" />
            ',

            'empty-courses' => '
                <path d="M4 6.3c2.3-.9 5-.9 7 .6 2-1.5 4.7-1.5 7-.6v12.4c-2.3-.9-5-.9-7 .6-2-1.5-4.7-1.5-7-.6Z" fill="currentColor" fill-opacity=".1" />
                <path d="M4 6.3c2.3-.9 5-.9 7 .6 2-1.5 4.7-1.5 7-.6v12.4c-2.3-.9-5-.9-7 .6-2-1.5-4.7-1.5-7-.6Z" />
                <path d="M11 6.9v12.4" />
            ',

            'empty-inbox' => '
                <path d="M4.5 8.5 3.5 13.5v5a1.5 1.5 0 0 0 1.5 1.5h14a1.5 1.5 0 0 0 1.5-1.5v-5l-1-5a1.5 1.5 0 0 0-1.5-1.2H6a1.5 1.5 0 0 0-1.5 1.2Z" fill="currentColor" fill-opacity=".1" />
                <path d="M4.5 8.5 3.5 13.5v5a1.5 1.5 0 0 0 1.5 1.5h14a1.5 1.5 0 0 0 1.5-1.5v-5l-1-5a1.5 1.5 0 0 0-1.5-1.2H6a1.5 1.5 0 0 0-1.5 1.2Z" />
                <path d="M3.5 13.5h4.8l1.4 2.3h4.6l1.4-2.3h4.8" />
            ',

            'empty-invoice' => '
                <path d="M6 3h12v17l-2.5-1.5L13 20l-2.5-1.5L8 20l-2-1.5Z" fill="currentColor" fill-opacity=".1" />
                <path d="M6 3h12v17l-2.5-1.5L13 20l-2.5-1.5L8 20l-2-1.5Z" />
                <path d="M9 8h6M9 11.3h6M9 14.6h3.5" />
            ',

            'empty-folder' => '
                <path d="M3.5 7.3a2 2 0 0 1 2-2h4.3l1.9 2.3h6.8a2 2 0 0 1 2 2v8.4a2 2 0 0 1-2 2H5.5a2 2 0 0 1-2-2Z" fill="currentColor" fill-opacity=".1" />
                <path d="M3.5 7.3a2 2 0 0 1 2-2h4.3l1.9 2.3h6.8a2 2 0 0 1 2 2v8.4a2 2 0 0 1-2 2H5.5a2 2 0 0 1-2-2Z" />
            ',

            'empty-document' => '
                <path d="M6.5 3h7l4 4v13a1 1 0 0 1-1 1h-10a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z" fill="currentColor" fill-opacity=".1" />
                <path d="M6.5 3h7l4 4v13a1 1 0 0 1-1 1h-10a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z" />
                <path d="M13.5 3v4h4" />
                <path d="M8.5 12.5h7M8.5 15.8h4.5" />
            ',

            'empty-building' => '
                <path d="M5 20V6.5a1 1 0 0 1 .5-.87L12 2l6.5 3.63a1 1 0 0 1 .5.87V20" fill="currentColor" fill-opacity=".1" />
                <path d="M5 20V6.5a1 1 0 0 1 .5-.87L12 2l6.5 3.63a1 1 0 0 1 .5.87V20" />
                <path d="M3.5 20h17M9.5 20v-4h5v4" />
                <path d="M9 9.5h1M14 9.5h1M9 13h1M14 13h1" />
            ',

            'empty-link' => '
                <path d="M9.5 14.5 14.5 9.5" />
                <path d="M11 6.5 12.6 4.9a3.3 3.3 0 0 1 4.7 4.7L15.7 11" />
                <path d="M13 17.5 11.4 19.1a3.3 3.3 0 0 1-4.7-4.7L8.3 13" />
            ',

            'empty-history' => '
                <circle cx="12" cy="12.5" r="8" fill="currentColor" fill-opacity=".1" />
                <circle cx="12" cy="12.5" r="8" />
                <path d="M12 8v4.5l3 2" />
                <path d="M6 4.5 4.5 3M4.5 3v2.3M4.5 3h2.3" />
            ',

            'empty-question' => '
                <circle cx="12" cy="12" r="9" fill="currentColor" fill-opacity=".1" />
                <circle cx="12" cy="12" r="9" />
                <path d="M9.3 9.3a2.7 2.7 0 1 1 3.8 2.5c-.9.4-1.1 1-1.1 1.9v.3" />
                <path d="M12 17h.01" stroke-width="2.6" />
            ',

            'empty-certificate' => '
                <circle cx="12" cy="9.3" r="5.8" fill="currentColor" fill-opacity=".1" />
                <circle cx="12" cy="9.3" r="5.8" />
                <path d="M9 13.6l-1.6 6 4.6-2.4 4.6 2.4-1.6-6" />
            ',

            'empty-results' => '
                <rect x="3.5" y="3.5" width="17" height="17" rx="2.3" fill="currentColor" fill-opacity=".1" />
                <rect x="3.5" y="3.5" width="17" height="17" rx="2.3" />
                <path d="M7.5 15.5v2M12 11.5v6M16.5 8.5v9" />
            ',

            'empty-send' => '
                <path d="M20.5 3.5 3 10.8l6.5 2.3M20.5 3.5 13.3 21l-3.8-7.9M20.5 3.5 9.5 13.1" fill="currentColor" fill-opacity=".08" />
                <path d="M20.5 3.5 3 10.8l6.5 2.3M20.5 3.5 13.3 21l-3.8-7.9M20.5 3.5 9.5 13.1" />
            ',

            'empty-search' => '
                <circle cx="10.5" cy="10.5" r="6.5" fill="currentColor" fill-opacity=".1" />
                <circle cx="10.5" cy="10.5" r="6.5" />
                <path d="M19.5 19.5 15.2 15.2" />
            ',

            'empty-layers' => '
                <path d="M12 3 3 8l9 5 9-5Z" fill="currentColor" fill-opacity=".1" />
                <path d="M12 3 3 8l9 5 9-5Z" />
                <path d="m3 12 9 5 9-5" />
                <path d="m3 16 9 5 9-5" />
            ',

            'empty-check' => '
                <circle cx="12" cy="12" r="9" fill="currentColor" fill-opacity=".1" />
                <circle cx="12" cy="12" r="9" />
                <path d="M8.3 12.2 11 15l4.8-5.4" />
            ',

            'empty-ticket' => '
                <path d="M3.5 9.3a2 2 0 0 1 2-2h13a2 2 0 0 1 2 2v1.2a2 2 0 0 0 0 3v1.2a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2v-1.2a2 2 0 0 0 0-3Z" fill="currentColor" fill-opacity=".1" />
                <path d="M3.5 9.3a2 2 0 0 1 2-2h13a2 2 0 0 1 2 2v1.2a2 2 0 0 0 0 3v1.2a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2v-1.2a2 2 0 0 0 0-3Z" />
                <path d="M14.5 7.5v9" stroke-dasharray="2 2.4" />
            ',

            'empty-tags' => '
                <path d="M11.5 3.5h5.8a1 1 0 0 1 1 1v5.8a1 1 0 0 1-.3.7l-8 8a1 1 0 0 1-1.4 0l-6.1-6.1a1 1 0 0 1 0-1.4l8-8a1 1 0 0 1 .7-.3Z" fill="currentColor" fill-opacity=".1" />
                <path d="M11.5 3.5h5.8a1 1 0 0 1 1 1v5.8a1 1 0 0 1-.3.7l-8 8a1 1 0 0 1-1.4 0l-6.1-6.1a1 1 0 0 1 0-1.4l8-8a1 1 0 0 1 .7-.3Z" />
                <circle cx="15" cy="7.5" r="1.3" fill="currentColor" stroke="none" />
            ',

            'empty-star' => '
                <path d="M12 3.5 14.6 9l6 .9-4.3 4.2 1 6-5.3-2.8-5.3 2.8 1-6-4.3-4.2 6-.9Z" fill="currentColor" fill-opacity=".1" />
                <path d="M12 3.5 14.6 9l6 .9-4.3 4.2 1 6-5.3-2.8-5.3 2.8 1-6-4.3-4.2 6-.9Z" />
            ',

            'empty-quote' => '
                <path d="M7.5 6.5c-2.3 1.3-3.5 3.3-3.5 6a3 3 0 0 0 3 3 2.7 2.7 0 0 0 2.7-2.7c0-1.4-1-2.4-2.3-2.5.2-1.6 1-2.7 2.3-3.5Z" fill="currentColor" fill-opacity=".1" />
                <path d="M7.5 6.5c-2.3 1.3-3.5 3.3-3.5 6a3 3 0 0 0 3 3 2.7 2.7 0 0 0 2.7-2.7c0-1.4-1-2.4-2.3-2.5.2-1.6 1-2.7 2.3-3.5Z" />
                <path d="M16.5 6.5c-2.3 1.3-3.5 3.3-3.5 6a3 3 0 0 0 3 3 2.7 2.7 0 0 0 2.7-2.7c0-1.4-1-2.4-2.3-2.5.2-1.6 1-2.7 2.3-3.5Z" fill="currentColor" fill-opacity=".1" />
                <path d="M16.5 6.5c-2.3 1.3-3.5 3.3-3.5 6a3 3 0 0 0 3 3 2.7 2.7 0 0 0 2.7-2.7c0-1.4-1-2.4-2.3-2.5.2-1.6 1-2.7 2.3-3.5Z" />
            ',

            'empty-news' => '
                <path d="M4.5 5.5h11a1 1 0 0 1 1 1v11.3a1.7 1.7 0 0 0 1.7 1.7H6.2a1.7 1.7 0 0 1-1.7-1.7Z" fill="currentColor" fill-opacity=".1" />
                <path d="M4.5 5.5h11a1 1 0 0 1 1 1v11.3a1.7 1.7 0 0 0 1.7 1.7H6.2a1.7 1.7 0 0 1-1.7-1.7Z" />
                <path d="M16.5 5.5h1.8a1.7 1.7 0 0 1 1.7 1.7v.8h-3.5" />
                <path d="M7.5 9h5M7.5 12h5M7.5 15h3" />
            ',

            'empty-image' => '
                <rect x="3" y="4.5" width="18" height="15" rx="2" fill="currentColor" fill-opacity=".1" />
                <rect x="3" y="4.5" width="18" height="15" rx="2" />
                <circle cx="8.5" cy="9.5" r="1.6" />
                <path d="m4 18 5-5 4 4 3-3 4 4" />
            ',

            'empty-handshake' => '
                <path d="M3 11 7 7l3 3-3.3 3.3Z" fill="currentColor" fill-opacity=".1" />
                <path d="M3 11 7 7l3 3-3.3 3.3Z" />
                <path d="M21 11 17 7l-3 3 3.3 3.3Z" fill="currentColor" fill-opacity=".1" />
                <path d="M21 11 17 7l-3 3 3.3 3.3Z" />
                <path d="M10 10 12.3 12.3a1.5 1.5 0 0 0 2.1 0v0a1.5 1.5 0 0 1 2.1 0L18 13.8" />
            ',

            'empty-calendar' => '
                <rect x="3.5" y="5" width="17" height="15.5" rx="2" fill="currentColor" fill-opacity=".1" />
                <rect x="3.5" y="5" width="17" height="15.5" rx="2" />
                <path d="M3.5 10h17" />
                <path d="M8 3v4M16 3v4" />
                <path d="M8 14h2M14 14h2M8 17h2M14 17h2" />
            ',

            'empty-announce' => '
                <path d="M4 9.8v4.4a1.6 1.6 0 0 0 1.6 1.6H7l3.8 3.1V5.1L7 8.2H5.6A1.6 1.6 0 0 0 4 9.8Z" fill="currentColor" fill-opacity=".1" />
                <path d="M4 9.8v4.4a1.6 1.6 0 0 0 1.6 1.6H7l3.8 3.1V5.1L7 8.2H5.6A1.6 1.6 0 0 0 4 9.8Z" />
                <path d="M14.3 8.1a4.3 4.3 0 0 1 0 7.8" />
                <path d="M17.2 5.9a7.8 7.8 0 0 1 0 12.2" />
            ',

            'empty-cart' => '
                <path d="M6.5 9h14L18.7 15.8a1.5 1.5 0 0 1-1.4 1H9.3a1.5 1.5 0 0 1-1.5-1.2L5.5 4.5H3.5" fill="currentColor" fill-opacity=".08" />
                <path d="M6.5 9h14L18.7 15.8a1.5 1.5 0 0 1-1.4 1H9.3a1.5 1.5 0 0 1-1.5-1.2L5.5 4.5H3.5" />
                <circle cx="10" cy="20" r="1.3" fill="currentColor" stroke="none" />
                <circle cx="17" cy="20" r="1.3" fill="currentColor" stroke="none" />
            ',

            'empty-contacts' => '
                <path d="M6 3.5h12a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1Z" fill="currentColor" fill-opacity=".1" />
                <path d="M6 3.5h12a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1Z" />
                <circle cx="12" cy="10.3" r="2.3" />
                <path d="M8.3 16.5c0-2.3 1.7-3.5 3.7-3.5s3.7 1.2 3.7 3.5" />
                <path d="M3 7.5h2M3 12h2M3 16.5h2" />
            ',
        ];
    }
}
