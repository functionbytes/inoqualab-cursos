<?php

namespace Tests\Feature\Distributors;

use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * El portal distributor (distribuidores viendo su propio panel) tenía cero
 * tests dedicados -- los archivos que parecían cubrirlo son en realidad de
 * Managers/Distributors y Supports/Distributors (Managers/Supports
 * gestionando distribuidores, dominio distinto).
 *
 * Regresión: EnterprisesController::destroy() redirigía a route('manager.enterprises')
 * (dominio Managers) en vez de la ruta equivalente de este portal. Un
 * distribuidor sin rol manager caía en el middleware IsManager y terminaba
 * redirigido a /validation en vez de volver a su propio listado de empresas.
 */
class EnterprisesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $distributorStaff;

    private Distributor $distributor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->distributor = Distributor::factory()->create();
        $this->distributorStaff = User::factory()->create(['role' => 'distributor']);
        DB::table('distributor_staff')->insert([
            'distributor_id' => $this->distributor->id,
            'user_id' => $this->distributorStaff->id,
        ]);
    }

    public function test_destroy_redirects_to_the_distributor_portal_not_managers(): void
    {
        $enterprise = Enterprise::factory()->create();
        DistributorEnterprise::create([
            'distributor_id' => $this->distributor->id,
            'enterprise_id' => $enterprise->id,
            'available' => 1,
        ]);

        // Antes: route('manager.enterprises') -- ruta del panel Managers, no
        // del portal distributor.
        $this->actingAs($this->distributorStaff)
            ->delete(route('distributor.enterprises.destroy', $enterprise->slack))
            ->assertRedirect(route('distributor.enterprises'));

        $this->assertSoftDeleted('enterprises', ['id' => $enterprise->id]);
    }

    public function test_destroy_of_unmanaged_enterprise_redirects_to_distributor_portal_with_error(): void
    {
        // Empresa que NO pertenece a este distribuidor.
        $foreignEnterprise = Enterprise::factory()->create();

        $this->actingAs($this->distributorStaff)
            ->delete(route('distributor.enterprises.destroy', $foreignEnterprise->slack))
            ->assertRedirect(route('distributor.enterprises'));

        $this->assertDatabaseHas('enterprises', ['id' => $foreignEnterprise->id, 'deleted_at' => null]);
    }
}
