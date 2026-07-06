<?php

return [

    'max_items' => 50000,

    'cache_duration' => 86400, // 24 horas en segundos

    /*
     * URLs estáticas que siempre aparecen en el sitemap principal.
     * 'loc' es relativo a la raíz del sitio (se pasa por url()).
     */
    'static_urls' => [
        ['loc' => '/',           'priority' => '1.0', 'changefreq' => 'daily'],
        ['loc' => '/courses',    'priority' => '0.9', 'changefreq' => 'daily'],
        ['loc' => '/blogs',      'priority' => '0.8', 'changefreq' => 'daily'],
        ['loc' => '/bundles',    'priority' => '0.8', 'changefreq' => 'weekly'],
        ['loc' => '/certifiers', 'priority' => '0.7', 'changefreq' => 'weekly'],
        ['loc' => '/instructions', 'priority' => '0.6', 'changefreq' => 'weekly'],
        ['loc' => '/about',      'priority' => '0.5', 'changefreq' => 'monthly'],
        ['loc' => '/faqs',       'priority' => '0.5', 'changefreq' => 'monthly'],
        ['loc' => '/contacts',   'priority' => '0.5', 'changefreq' => 'monthly'],
        ['loc' => '/terms',      'priority' => '0.3', 'changefreq' => 'yearly'],
        ['loc' => '/politics',   'priority' => '0.3', 'changefreq' => 'yearly'],
        // '/commercial' excluido: aún tiene contenido demo de la plantilla (ver CommercialController, noindex).
    ],

];
