<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'aws' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'version' => env('AWS_VERSION', 'latest'),
    ],

    'wompi' => [
        'public_key' => env('WOMPI_PUBLIC_KEY', ''),
        'integrity_secret' => env('WOMPI_INTEGRITY_SECRET', ''),
        'events_secret' => env('WOMPI_EVENTS_SECRET', ''),
        'sandbox' => env('WOMPI_SANDBOX', true),
    ],

    // Traducción automática de metadatos SEO (panel/seo/metas).
    'deepl' => [
        'key' => env('DEEPL_API_KEY', ''),
    ],

    // Google Search Console: OAuth para traer clicks/impresiones a seo_metas.
    // El refresh token no vive aquí, se guarda en storage/app/seo-gsc.json.
    'gsc' => [
        'client_id' => env('GSC_CLIENT_ID', ''),
        'client_secret' => env('GSC_CLIENT_SECRET', ''),
        // Sin GSC_PROPERTY_URL cae al dominio de la app. Se resuelve aquí y no
        // con el segundo argumento de config(): declarada la clave, ese default
        // ya no se aplicaría nunca (la clave existe, aunque valga null).
        'property_url' => env('GSC_PROPERTY_URL') ?: env('APP_URL'),
    ],

];
