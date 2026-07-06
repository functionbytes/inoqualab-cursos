<?php

namespace Tests\Feature\Managers;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Techo de privilegios de RolesController: quien tiene roles.create/update no
 * puede otorgar permisos que él mismo no posee (evita auto-escalada), y los
 * permisos de los roles protegidos no se modifican desde esa pantalla.
 */
class RolesCeilingTest extends TestCase
{
    use RefreshDatabase;

    private function limitedActor(): User
    {
        $user = User::factory()->manager()->create();
        // Se quita el rol Spatie `manager` (que otorga orders.delete) para que el
        // actor tenga SOLO permisos directos: gestionar roles y ver órdenes, pero
        // NO borrarlas. La columna role='manager' se mantiene para el middleware.
        $user->syncRoles([]);
        $user->syncPermissions(['roles.create', 'roles.update', 'orders.view']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_actor_cannot_grant_a_permission_they_do_not_hold(): void
    {
        $actor = $this->limitedActor();
        $allowed = Permission::where('name', 'orders.view')->value('id');
        $forbidden = Permission::where('name', 'orders.delete')->value('id');

        $this->actingAs($actor)
            ->post(route('manager.roles.store'), [
                'name' => 'Rol de prueba',
                'permissions' => [$allowed, $forbidden],
            ])
            ->assertRedirect();

        $role = Role::where('name', 'Rol de prueba')->first();
        $this->assertNotNull($role);
        // Se otorgó el que el actor posee, se filtró el que no.
        $this->assertTrue($role->hasPermissionTo('orders.view'));
        $this->assertFalse($role->hasPermissionTo('orders.delete'));
    }

    public function test_protected_role_permissions_are_not_modified(): void
    {
        $actor = $this->limitedActor();
        $superadmin = Role::where('name', 'superadmin')->firstOrFail();
        $before = $superadmin->permissions()->count();
        $this->assertGreaterThan(0, $before);

        // Intentar vaciar los permisos de superadmin vía update.
        $this->actingAs($actor)
            ->put(route('manager.roles.update', $superadmin->id), [
                'permissions' => [],
            ])
            ->assertRedirect();

        $this->assertSame($before, $superadmin->fresh()->permissions()->count());
    }
}
