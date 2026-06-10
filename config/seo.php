<?php

return [

    /*
     * Sufijo añadido al title en cada página.
     * Se puede sobreescribir desde el panel en Settings > SEO.
     */
    'title_suffix' => ' | '.env('APP_NAME', 'Training'),

    /*
     * Directiva robots por defecto para todas las páginas.
     */
    'default_robots' => 'index,follow',

    /*
     * Tipo OG por defecto.
     */
    'default_og_type' => 'website',

    /*
     * Twitter card por defecto.
     */
    'default_twitter_card' => 'summary_large_image',

    /*
     * Robots.txt por defecto (cuando no hay contenido guardado en BD).
     */
    'robots_txt_default' => implode("\n", [
        'User-agent: *',
        'Allow: /',
        'Disallow: /panel/',
        'Disallow: /api/',
        '',
        'Sitemap: '.env('APP_URL', '').'/sitemap.xml',
    ]),

    /*
     * llms.txt por defecto (para crawlers de IA).
     */
    'llms_txt_enabled' => true,

];
