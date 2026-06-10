<?php

use App\Services\IncomingMail\Parsers\RedNacionalParser;

return [

    /*
    |--------------------------------------------------------------------------
    | Interceptación de correos entrantes → órdenes
    |--------------------------------------------------------------------------
    |
    | Configuración del flujo que lee un buzón IMAP, parsea los correos de
    | remitentes confiables y genera órdenes + matrículas de forma automática
    | (confianza alta) o las envía a la bandeja de revisión (confianza dudosa).
    |
    */

    // Habilita/deshabilita el Command de polling sin tocar el scheduler.
    'enabled' => env('INCOMING_MAIL_ENABLED', true),

    // Si true, las órdenes con confianza >= threshold se crean automáticamente.
    // Si false, TODO va a la bandeja de revisión.
    'auto_process' => env('INCOMING_MAIL_AUTO_PROCESS', true),

    // Umbral de confianza (0-100) para auto-procesar. Por debajo => revisión.
    'confidence_threshold' => (int) env('INCOMING_MAIL_CONFIDENCE_THRESHOLD', 90),

    // Carpeta IMAP a leer.
    'folder' => env('INCOMING_MAIL_FOLDER', 'INBOX'),

    // Cuenta IMAP (definida en config/imap.php) a usar.
    'imap_account' => env('IMAP_DEFAULT_ACCOUNT', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Remitentes confiables → parser
    |--------------------------------------------------------------------------
    |
    | Solo se procesan correos cuyo remitente esté en esta whitelist. Cada
    | remitente se asocia a una clase Parser (estrategia) que sabe extraer
    | los datos de ese formato concreto. Hoy solo San Diego / rednacional.
    |
    */
    'parsers' => [
        'noreply@rednacional.com.co' => RedNacionalParser::class,
    ],

    // Lista plana de remitentes confiables (derivada de 'parsers' + extra .env).
    // Cualquier remitente fuera de esta lista se marca como 'ignored'.
    'trusted_senders' => array_values(array_filter(
        array_unique(array_merge(
            ['noreply@rednacional.com.co'],
            array_map('trim', explode(',', (string) env('INCOMING_MAIL_TRUSTED_SENDERS', '')))
        ))
    )),

    // ⚠️ SOLO PRUEBAS: si es true, procesa correos de CUALQUIER remitente
    // (ignora la whitelist) usando 'default_parser'. REVERTIR antes de producción.
    'trust_all_senders' => (bool) env('INCOMING_MAIL_TRUST_ALL_SENDERS', false),

    // Parser usado cuando trust_all_senders está activo y el remitente no tiene
    // un parser específico mapeado en 'parsers'.
    'default_parser' => RedNacionalParser::class,

    /*
    |--------------------------------------------------------------------------
    | Defaults para la orden generada
    |--------------------------------------------------------------------------
    |
    | Slugs de OrderType / OrderMethod / OrderCondition usados al crear la
    | orden desde un correo (replican InscriptionsController::store).
    |
    */
    'order_defaults' => [
        'type_slug' => 'services',
        'method_slug' => 'credit',
        'condition_slug' => 'payment',
    ],

    // Rol por defecto para usuarios (alumnos) creados desde un correo.
    'student_role' => 'customer',
];
