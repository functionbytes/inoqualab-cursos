<?php

namespace Tests\Feature\Managers;

use App\Http\Middleware\EnforcePanelPermission;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Invariante de seguridad del panel: EnforcePanelPermission es "fail-open" (deja
 * pasar si el permiso derivado del nombre de ruta no existe en el catálogo). Este
 * test garantiza que TODA ruta del panel manager deriva un permiso que SÍ existe,
 * de modo que un dominio nuevo sin permiso sembrado no quede abierto sin ruido.
 */
class PanelPermissionInvariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_manager_panel_route_maps_to_an_existing_permission(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $middleware = new EnforcePanelPermission;
        $derive = new ReflectionMethod($middleware, 'permissionForRoute');
        $derive->setAccessible(true);

        $orphans = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if ($name === null || ! str_starts_with($name, 'manager.')) {
                continue;
            }

            $isReadOnly = in_array('GET', $route->methods(), true);
            $permission = $derive->invoke($middleware, $name, $isReadOnly);

            // null = fail-open por diseño (no derivable). Solo nos importan los
            // permisos derivados que NO existen: esos quedan abiertos en silencio.
            if ($permission === null) {
                continue;
            }

            if (! Permission::where('name', $permission)->where('guard_name', 'web')->exists()) {
                $orphans[] = "{$name} -> {$permission}";
            }
        }

        $this->assertEmpty(
            $orphans,
            "Rutas del panel cuyo permiso derivado NO existe (fail-open silencioso):\n".implode("\n", $orphans)
        );
    }
}
