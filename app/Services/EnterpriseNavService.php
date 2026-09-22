<?php

namespace App\Services;

use App\Services\Concerns\BaseNavService;

/**
 * Navegación del portal empresa en formato "icon rail + panel lateral" (mismo
 * patrón que ManagerNavService). Portado 1:1 desde el menú vertical clásico
 * anterior en enterprises/includes/nav.blade.php: 6 links planos, sin
 * permisos Spatie ni gates de setting() (este portal aún no los usa).
 */
class EnterpriseNavService extends BaseNavService
{
    protected static function menu(): array
    {
        return [
            'miniItems' => [
                ['id' => 'dashboard', 'icon' => 'home', 'tooltip' => 'Inicio', 'sidebar_id' => 'dashboard', 'url' => 'enterprise.dashboard', 'order' => 1],
                ['id' => 'courses', 'icon' => 'courses', 'tooltip' => 'Cursos', 'sidebar_id' => 'courses', 'order' => 2],
                ['id' => 'documents', 'icon' => 'folder', 'tooltip' => 'Documentos', 'sidebar_id' => 'documents', 'order' => 3],
                ['id' => 'users', 'icon' => 'users', 'tooltip' => 'Usuarios', 'sidebar_id' => 'users', 'order' => 4],
                ['id' => 'enterprise', 'icon' => 'building', 'tooltip' => 'Empresa', 'sidebar_id' => 'enterprise', 'order' => 5],
                ['id' => 'settings', 'icon' => 'sliders', 'tooltip' => 'Configuración', 'sidebar_id' => 'settings', 'order' => 6],
            ],
            'sidebars' => [
                'courses' => [
                    'sections' => [[
                        'title' => 'Cursos',
                        'items' => [
                            ['label' => 'Cursos', 'route' => 'enterprise.courses'],
                        ],
                    ]],
                ],
                'documents' => [
                    'sections' => [[
                        'title' => 'Documentos',
                        'items' => [
                            ['label' => 'Documentos', 'route' => 'enterprise.documents'],
                        ],
                    ]],
                ],
                'users' => [
                    'sections' => [[
                        'title' => 'Usuarios',
                        'items' => [
                            ['label' => 'Usuarios', 'route' => 'enterprise.users'],
                        ],
                    ]],
                ],
                'enterprise' => [
                    'sections' => [[
                        'title' => 'Empresa',
                        'items' => [
                            ['label' => 'Empresa', 'route' => 'enterprise.enterprises'],
                        ],
                    ]],
                ],
                'settings' => [
                    'sections' => [[
                        'title' => 'Configuración',
                        'items' => [
                            ['label' => 'Configuración', 'route' => 'enterprise.profile'],
                        ],
                    ]],
                ],
            ],
        ];
    }
}
