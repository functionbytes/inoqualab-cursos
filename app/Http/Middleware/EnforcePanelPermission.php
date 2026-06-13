<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autorización por convención para el panel manager.
 *
 * Deriva el permiso requerido del nombre de la ruta:
 *   manager.{dominio}.{accion}  ->  {dominio}.{view|create|update|delete}
 *
 * Si ese permiso existe en el sistema y el usuario no lo tiene, responde 403.
 * Si el permiso no existe (dominios sin permisos definidos), deja pasar.
 *
 * Reemplaza decenas de `->middleware('permission:...')` repartidos por las
 * rutas: basta con que el nombre de ruta siga la convención del proyecto.
 */
class EnforcePanelPermission
{
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
        if ($routeName === null || ! str_starts_with($routeName, 'manager.')) {
            return null;
        }

        $parts = explode('.', $routeName);

        if (count($parts) < 2) {
            return null;
        }

        $domain = $parts[1];
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
