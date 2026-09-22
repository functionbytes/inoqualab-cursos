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
        ];
    }
}
