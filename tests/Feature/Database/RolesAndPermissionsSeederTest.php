<?php

namespace Tests\Feature\Database;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresión: el rol 'distributor' recibía 'inscriptions.*', que expande a
 * 'inscriptions.manage'. ChecksOwnership (usado por InscriptionPolicy)
 * bypasea el ownership check completo para cualquier actor con '{alias}.manage'
 * -- si esa Policy llega a cablearse a un controller algún día, CUALQUIER
 * distribuidor autenticado tendría acceso a las inscripciones de TODOS los
 * distribuidores, no solo las propias, con solo cambiar el id en la URL.
 * Hoy no es explotable (InscriptionPolicy no está cableada a ningún
 * controller), pero el permiso en sí ya es un exceso de privilegio que no
 * debería sembrarse.
 */
class RolesAndPermissionsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_distributor_role_does_not_have_inscriptions_manage_permission(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $distributor = Role::where('name', 'distributor')->where('guard_name', 'web')->first();

        $this->assertNotNull($distributor);
        $this->assertFalse($distributor->hasPermissionTo('inscriptions.manage'));
    }

    public function test_distributor_role_still_has_the_other_inscriptions_actions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $distributor = Role::where('name', 'distributor')->where('guard_name', 'web')->first();

        foreach (['view', 'create', 'update', 'delete'] as $action) {
            $this->assertTrue(
                $distributor->hasPermissionTo("inscriptions.{$action}"),
                "El rol distributor debería conservar inscriptions.{$action}."
            );
        }
    }
}
