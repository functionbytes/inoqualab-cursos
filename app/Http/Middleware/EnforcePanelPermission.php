<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autorización por convención para los paneles internos.
 *
 * Deriva el permiso requerido del nombre de la ruta:
 *   {panel}.{dominio}.{accion}  ->  {dominio}.{view|create|update|delete}
 *
 * donde {panel} es uno de: manager, support, distributor, enterprise, accounting.
 *
 * Si ese permiso existe en el sistema y el usuario no lo tiene, responde 403.
 * Si el permiso no existe (dominios sin permisos definidos), deja pasar.
 *
 * Reemplaza decenas de `->middleware('permission:...')` repartidos por las
 * rutas: basta con que el nombre de ruta siga la convención del proyecto.
 */
class EnforcePanelPermission
{
    /** Prefijos de nombre de ruta sujetos a autorización por convención. */
    private const PANEL_PREFIXES = ['manager', 'support', 'distributor', 'enterprise', 'accounting'];

    /**
     * Alias de dominio: nombre de ruta -> alias de permiso real.
     *
     * Cubre los nombres de ruta que no coinciden 1:1 con el catálogo de permisos:
     * singulares (`order` -> `orders`), nombres cortos (`mails` -> `incoming-mails`)
     * y erratas históricas (`departaments` -> `departments`). Evita el fail-open
     * sin tener que renombrar rutas ya referenciadas por `route()`.
     */
    private const DOMAIN_ALIASES = [
        'newsletter' => 'newsletters',
        'order' => 'orders',
        'mails' => 'incoming-mails',
        'departaments' => 'departments',
    ];

    /** Sufijo de acción de ruta -> verbo de permiso. */
    private const ACTION_MAP = [
        'index' => 'view', 'show' => 'view', 'view' => 'view', 'list' => 'view',
        'navegation' => 'view', 'report' => 'view', 'resume' => 'view', 'export' => 'view',
        'create' => 'create', 'store' => 'create', 'duplicate' => 'create', 'clone' => 'create',
        'edit' => 'update', 'update' => 'update',
        'destroy' => 'delete', 'delete' => 'delete', 'bulk-action' => 'delete',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $permission = $this->permissionForRoute($request->route()?->getName());

        if ($permission !== null && $this->permissionExists($permission)) {
            if (! $request->user()?->can($permission)) {
                abort(403);
            }
        }

        return $next($request);
    }

    private function permissionForRoute(?string $routeName): ?string
    {
        if ($routeName === null) {
            return null;
        }

        $parts = explode('.', $routeName);

        if (count($parts) < 2 || ! in_array($parts[0], self::PANEL_PREFIXES, true)) {
            return null;
        }

        $domain = self::DOMAIN_ALIASES[$parts[1]] ?? $parts[1];
        $action = end($parts);
        $verb = self::ACTION_MAP[$action] ?? 'view';

        return "{$domain}.{$verb}";
    }

    private function permissionExists(string $permission): bool
    {
        return app(PermissionRegistrar::class)
            ->getPermissions()
            ->contains(fn ($p) => $p->name === $permission && $p->guard_name === 'web');
    }
}
