<?php

namespace Tests\Feature\Distributors\Enterprises;

use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresión: reassignAll() llamaba a User::identification($identification)
 * dentro de un foreach masivo. Ese scope aborta con 404 si no hay match --
 * correcto para una búsqueda puntual, pero en este loop una sola
 * identificación con typo o de un usuario ya borrado hacía reventar el
 * request COMPLETO a mitad de camino: las reasignaciones ya guardadas
 * (sin transacción) quedaban hechas, las siguientes nunca se procesaban, y
 * el distribuidor solo veía un 404 sin ninguna pista de qué pasó.
 */
class ReassignControllerTest extends TestCase
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

    private function attachEnterprise(): Enterprise
    {
        $enterprise = Enterprise::factory()->create();
        DistributorEnterprise::create([
            'distributor_id' => $this->distributor->id,
            'enterprise_id' => $enterprise->id,
            'available' => 1,
        ]);

        return $enterprise;
    }

    public function test_a_nonexistent_identification_in_the_list_does_not_abort_the_whole_batch(): void
    {
        $oldEnterprise = $this->attachEnterprise();
        $newEnterprise = $this->attachEnterprise();

        $validUser = User::factory()->create(['identification' => 'VALID123']);
        EnterpriseUser::create([
            'user_id' => $validUser->id,
            'enterprise_id' => $oldEnterprise->id,
            'available' => 1,
        ]);

        $this->actingAs($this->distributorStaff)
            ->postJson(route('distributor.enterprises.users.reassign.all'), [
                'enterprise' => $newEnterprise->slack,
                // 'NO-EXISTE' no pertenece a ningún usuario -- antes del fix,
                // esto abortaba con 404 antes de siquiera intentar VALID123.
                'users' => 'NO-EXISTE,VALID123',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('enterprise_user', [
            'user_id' => $validUser->id,
            'enterprise_id' => $newEnterprise->id,
        ]);
    }

    public function test_reassigns_all_valid_users_in_the_list(): void
    {
        $oldEnterprise = $this->attachEnterprise();
        $newEnterprise = $this->attachEnterprise();

        $userA = User::factory()->create(['identification' => 'USERA']);
        $userB = User::factory()->create(['identification' => 'USERB']);
        foreach ([$userA, $userB] as $u) {
            EnterpriseUser::create([
                'user_id' => $u->id,
                'enterprise_id' => $oldEnterprise->id,
                'available' => 1,
            ]);
        }

        $this->actingAs($this->distributorStaff)
            ->postJson(route('distributor.enterprises.users.reassign.all'), [
                'enterprise' => $newEnterprise->slack,
                'users' => 'USERA,USERB',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('enterprise_user', ['user_id' => $userA->id, 'enterprise_id' => $newEnterprise->id]);
        $this->assertDatabaseHas('enterprise_user', ['user_id' => $userB->id, 'enterprise_id' => $newEnterprise->id]);
    }
}
