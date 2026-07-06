<?php

namespace Tests\Feature\Managers\Distributors;

use App\Models\Distributor\Distributor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributorsTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Distribuidor de prueba',
            'nit' => '900123456-1',
            'address' => 'Calle 1 # 2-3',
            'cellphone' => '3001234567',
            'email' => 'nuevo@distribuidor.com',
            'leading' => 'Juan Perez',
            'supporting' => 'Maria Lopez',
        ], $overrides);
    }

    public function test_manager_can_create_distributor_with_valid_data(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.store'), $this->validPayload())
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('distributors', ['email' => 'nuevo@distribuidor.com']);
    }

    public function test_store_rejects_empty_payload(): void
    {
        $response = $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.store'), [])
            ->assertStatus(422);

        $response->assertJsonValidationErrors([
            'title', 'nit', 'address', 'cellphone', 'email', 'leading', 'supporting',
        ]);

        $this->assertDatabaseCount('distributors', 0);
    }

    public function test_store_rejects_invalid_email_format(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.store'), $this->validPayload(['email' => 'not-an-email']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_rejects_non_numeric_cellphone(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.store'), $this->validPayload(['cellphone' => 'abc-not-a-phone']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cellphone']);
    }

    public function test_update_rejects_nonexistent_slack(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.update'), $this->validPayload([
                'slack' => 'does-not-exist',
                'available' => '1',
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slack']);
    }

    public function test_manager_can_update_distributor_with_valid_data(): void
    {
        $distributor = Distributor::factory()->create();

        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.update'), $this->validPayload([
                'slack' => $distributor->slack,
                'email' => $distributor->email,
                'nit' => $distributor->nit,
                'available' => '1',
            ]))
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_unauthorized_user_cannot_create_distributor(): void
    {
        $restrictedManager = User::factory()->manager()->create();
        $restrictedManager->syncRoles([]);

        $this->actingAs($restrictedManager)
            ->postJson(route('manager.distributors.store'), $this->validPayload())
            ->assertForbidden();
    }
}
