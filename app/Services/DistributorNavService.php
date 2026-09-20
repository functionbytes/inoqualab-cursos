<?php

namespace App\Services;

use App\Services\Concerns\BaseNavService;

/**
 * Navegación del portal distribuidor en formato "icon rail + panel lateral"
 * (mismo patrón que ManagerNavService / SupportNavService). Portado 1:1
 * desde el menú clásico anterior en distributors/includes/nav.blade.php:
 * mismos items, mismas rutas, sin agregar ni quitar nada — solo
 * reorganizado por dominio para el rail de iconos.
 */
class DistributorNavService extends BaseNavService
{
    /**
     * Estructura completa del menú: mini-items (rail de iconos) + sidebars
     * (paneles laterales con secciones e items).
     */
    protected static function menu(): array
    {
        return [
            'miniItems' => [
                ['id' => 'home', 'icon' => 'fa-duotone fa-house', 'tooltip' => 'Inicio', 'sidebar_id' => 'home', 'url' => 'distributor.dashboard', 'order' => 1],
                ['id' => 'enterprises', 'icon' => 'fa-duotone fa-building', 'tooltip' => 'Empresas', 'sidebar_id' => 'enterprises', 'order' => 2],
                ['id' => 'registers', 'icon' => 'fa-duotone fa-user-plus', 'tooltip' => 'Crear usuario', 'sidebar_id' => 'registers', 'order' => 3],
                ['id' => 'inscriptions', 'icon' => 'fa-duotone fa-list-check', 'tooltip' => 'Inscripciones', 'sidebar_id' => 'inscriptions', 'order' => 4],
                ['id' => 'invoices', 'icon' => 'fa-duotone fa-file-invoice', 'tooltip' => 'Facturas', 'sidebar_id' => 'invoices', 'order' => 5],
                ['id' => 'orders', 'icon' => 'fa-duotone fa-receipt', 'tooltip' => 'Ordenes', 'sidebar_id' => 'orders', 'order' => 6],
                ['id' => 'settings', 'icon' => 'fa-duotone fa-gear', 'tooltip' => 'Configuración', 'sidebar_id' => 'settings', 'order' => 7],
            ],
            'sidebars' => [
                'enterprises' => [
                    'sections' => [[
                        'title' => 'Empresas',
                        'items' => [
                            ['label' => 'Empresas', 'route' => 'distributor.enterprises'],
                        ],
                    ]],
                ],
                'registers' => [
                    'sections' => [[
                        'title' => 'Crear usuario',
                        'items' => [
                            ['label' => 'Crear usuario', 'route' => 'distributor.registers'],
                        ],
                    ]],
                ],
                'inscriptions' => [
                    'sections' => [[
                        'title' => 'Inscripciones',
                        'items' => [
                            ['label' => 'Inscripciones', 'route' => 'distributor.inscriptions'],
                            ['label' => 'Inscripciones masiva', 'route' => 'distributor.inscriptions.massives'],
                        ],
                    ]],
                ],
                'invoices' => [
                    'sections' => [[
                        'title' => 'Facturas',
                        'items' => [
                            ['label' => 'Facturas', 'route' => 'distributor.invoices'],
                        ],
                    ]],
                ],
                'orders' => [
                    'sections' => [[
                        'title' => 'Ordenes',
                        'items' => [
                            ['label' => 'Ordenes', 'route' => 'distributor.orders'],
                        ],
                    ]],
                ],
                'settings' => [
                    'sections' => [[
                        'title' => 'Configuración',
                        'items' => [
                            ['label' => 'Distribuidor', 'route' => 'distributor.settings.distributor'],
                            ['label' => 'Usuario', 'route' => 'distributor.settings.profile'],
                            ['label' => 'Notificaciones', 'route' => 'distributor.settings.notifications'],
                        ],
                    ]],
                ],
            ],
        ];
    }
}
