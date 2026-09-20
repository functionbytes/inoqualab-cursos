<?php

namespace App\Services;

use App\Services\Concerns\BaseNavService;

/**
 * Navegación del portal contabilidad en formato "icon rail + panel lateral"
 * (mismo patrón que App\Services\ManagerNavService). La lógica de detección
 * de item/sidebar activo vive en BaseNavService, compartida por todos los
 * portales.
 *
 * Portado 1:1 desde el menú anterior en accountings/includes/nav.blade.php:
 * ese menú era completamente plano (Inicio, Distribuidores, Facturas,
 * Ordenes, Configuración — sin ítems anidados ni permisos @can), así que
 * cada dominio es un único mini-item de link directo (mismo patrón que
 * "Dashboard" en ManagerNavService): no hace falta panel lateral porque
 * ningún dominio tenía sub-páginas en el nav original.
 *
 * Único cambio respecto al nav original: "Inicio" apuntaba a route('home')
 * (la home pública), un enlace incorrecto — el breadcrumb del propio portal
 * (accountings/includes/card.blade.php) ya usa 'accounting.dashboard' para
 * "Inicio", así que se corrige aquí para que apunte al dashboard real del
 * portal, igual que el mini-item "Dashboard" de managers apunta a
 * 'manager.dashboard'.
 */
class AccountingNavService extends BaseNavService
{
    protected static function menu(): array
    {
        return [
            'miniItems' => [
                ['id' => 'dashboard', 'icon' => 'fa-duotone fa-house', 'tooltip' => 'Inicio', 'sidebar_id' => 'dashboard', 'url' => 'accounting.dashboard', 'order' => 1],
                ['id' => 'distributors', 'icon' => 'fa-duotone fa-users', 'tooltip' => 'Distribuidores', 'sidebar_id' => 'distributors', 'url' => 'accounting.distributors', 'order' => 2],
                ['id' => 'invoices', 'icon' => 'fa-duotone fa-file-invoice', 'tooltip' => 'Facturas', 'sidebar_id' => 'invoices', 'url' => 'accounting.invoices', 'order' => 3],
                ['id' => 'orders', 'icon' => 'fa-duotone fa-receipt', 'tooltip' => 'Órdenes', 'sidebar_id' => 'orders', 'url' => 'accounting.orders', 'order' => 4],
                ['id' => 'settings', 'icon' => 'fa-duotone fa-gear-code', 'tooltip' => 'Configuración', 'sidebar_id' => 'settings', 'url' => 'accounting.profile', 'order' => 5],
            ],
            // Ningún dominio tenía sub-páginas en el nav original: todos los
            // mini-items son links directos (llevan 'url'), así que no hace
            // falta declarar paneles laterales.
            'sidebars' => [],
        ];
    }
}
