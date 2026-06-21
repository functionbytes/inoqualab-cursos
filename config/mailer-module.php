<?php

return [
    'category_labels' => [
        'system' => 'Sistema',
        'site' => 'Sitio',
        'user' => 'Usuario',
        'company' => 'Empresa',
        'order' => 'Pedido',
        'date' => 'Fecha',
        'links' => 'Enlaces',
        'newsletter' => 'Newsletter',
        'general' => 'General',
    ],

    'modules' => ['core', 'orders', 'notifications', 'newsletter'],

    'protected_aliases' => [
        'email_template_header',
        'email_template_footer',
        'email_template_wrapper',
    ],

    'cache_layout_aliases' => [
        'email_template_header',
        'email_template_footer',
        'email_template_wrapper',
    ],

    'queue' => 'emails',

    'limits' => [
        'payload_max_bytes' => 262144,
        'subject_max_length' => 255,
        'body_max_bytes' => 5242880,
    ],

    'retention' => [
        'logs_days' => 90,
        'versions_per_template' => 50,
    ],

    'reserved_slugs' => ['send', 'info', 'status', 'logs'],
];
