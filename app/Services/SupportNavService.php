<?php

namespace App\Services;

use App\Services\Concerns\BaseNavService;

/**
 * Navegación del portal support en formato "icon rail + panel lateral"
 * (mismo patrón que ManagerNavService). Portado 1:1 desde el menú clásico
 * anterior en supports/includes/nav.blade.php: mismos items, mismas rutas,
 * sin agregar ni quitar nada — solo reorganizado por dominio para el rail
 * de iconos.
 */
class SupportNavService extends BaseNavService
{
    /**
     * Estructura completa del menú: mini-items (rail de iconos) + sidebars
     * (paneles laterales con secciones e items).
     */
    protected static function menu(): array
    {
        return [
            'miniItems' => [
                ['id' => 'home', 'icon' => 'fa-duotone fa-house', 'tooltip' => 'Inicio', 'sidebar_id' => 'home', 'url' => 'support.dashboard', 'order' => 1],
                ['id' => 'enterprises', 'icon' => 'fa-duotone fa-building', 'tooltip' => 'Empresas', 'sidebar_id' => 'enterprises', 'order' => 2],
                ['id' => 'distributors', 'icon' => 'fa-duotone fa-people-arrows', 'tooltip' => 'Distribuidor', 'sidebar_id' => 'distributors', 'order' => 3],
                ['id' => 'users', 'icon' => 'fa-duotone fa-users', 'tooltip' => 'Usuarios', 'sidebar_id' => 'users', 'order' => 4],
                ['id' => 'mails', 'icon' => 'fa-duotone fa-inbox', 'tooltip' => 'Correos entrantes', 'sidebar_id' => 'mails', 'order' => 5],
                ['id' => 'system', 'icon' => 'fa-duotone fa-headset', 'tooltip' => 'Configuración Sistema', 'sidebar_id' => 'system', 'order' => 6],
                ['id' => 'settings', 'icon' => 'fa-duotone fa-gear', 'tooltip' => 'Configuración', 'sidebar_id' => 'settings', 'order' => 7],
            ],
            'sidebars' => [
                'enterprises' => [
                    'sections' => [[
                        'title' => 'Empresas',
                        'items' => [
                            ['label' => 'Empresas', 'route' => 'support.enterprises'],
                        ],
                    ]],
                ],
                'distributors' => [
                    'sections' => [[
                        'title' => 'Distribuidor',
                        'items' => [
                            ['label' => 'Distribuidor', 'route' => 'support.distributors'],
                        ],
                    ]],
                ],
                'users' => [
                    'sections' => [[
                        'title' => 'Usuarios',
                        'items' => [
                            ['label' => 'Usuarios', 'route' => 'support.users'],
                        ],
                    ]],
                ],
                'mails' => [
                    'sections' => [[
                        'title' => 'Correos',
                        'items' => [
                            ['label' => 'Correos entrantes', 'route' => 'support.mails.index'],
                        ],
                    ]],
                ],
                'system' => [
                    'sections' => [[
                        'title' => 'Configuración Sistema',
                        'items' => [
                            ['label' => 'Contacto', 'route' => 'support.contacts'],
                            ['label' => 'Preguntas frecuentes', 'route' => 'support.faqs'],
                            ['label' => 'Documentos', 'route' => 'support.documents'],
                        ],
                    ]],
                ],
                'settings' => [
                    'sections' => [[
                        'title' => 'Configuración',
                        'items' => [
                            ['label' => 'Usuario', 'route' => 'support.settings.profile'],
                            ['label' => 'Notificaciones', 'route' => 'support.settings.notifications'],
                        ],
                    ]],
                ],
            ],
        ];
    }
}
