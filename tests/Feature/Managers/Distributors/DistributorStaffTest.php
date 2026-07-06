<?php

namespace Tests\Feature\Managers\Distributors;

use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorStaff;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributorStaffTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected Distributor $distributor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
        $this->distributor = Distributor::factory()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'distributor' => $this->distributor->slack,
            'firstname' => 'Carlos',
            'lastname' => 'Ramirez',
            'email' => 'empleado@distribuidor.com',
            'password' => 'password123',
        ], $overrides);
    }

    public function test_manager_can_create_staff_with_valid_data(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.staffs.store'), $this->validPayload())
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['email' => 'empleado@distribuidor.com', 'role' => 'distributor']);
    }

    public function test_store_rejects_empty_payload(): void
    {
        $response = $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.staffs.store'), [])
            ->assertStatus(422);

        $response->assertJsonValidationErrors(['distributor', 'firstname', 'lastname', 'email', 'password']);

        $this->assertDatabaseCount('users', 1); // solo el manager creado en setUp
    }

    public function test_store_rejects_missing_password(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.staffs.store'), $this->validPayload(['password' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_store_rejects_nonexistent_distributor(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.staffs.store'), $this->validPayload(['distributor' => 'does-not-exist']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['distributor']);
    }

    public function test_manager_can_update_staff_with_valid_data(): void
    {
        $user = User::factory()->distributor()->create();
        DistributorStaff::create([
            'user_id' => $user->id,
            'distributor_id' => $this->distributor->id,
            'available' => 1,
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.staffs.update'), [
                'slack' => $user->slack,
                'firstname' => 'Actualizado',
                'lastname' => $user->lastname,
                'email' => $user->email,
                'available' => '1',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_unauthorized_user_cannot_create_staff(): void
    {
        $restrictedManager = User::factory()->manager()->create();
        $restrictedManager->syncRoles([]);

        $this->actingAs($restrictedManager)
            ->postJson(route('manager.distributors.staffs.store'), $this->validPayload())
            ->assertForbidden();
    }
}
