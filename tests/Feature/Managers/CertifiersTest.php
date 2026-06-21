<?php

namespace Tests\Feature\Managers;

use App\Models\Certifier;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CertifiersTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    private function createCertifier(array $overrides = []): Certifier
    {
        return Certifier::create(array_merge([
            'slack' => (string) Str::uuid(),
            'firstname' => 'JUAN',
            'lastname' => 'PEREZ',
            'identification' => '123456789',
            'profession' => 'Ingeniero',
            'available' => 1,
        ], $overrides));
    }

    public function test_manager_can_list_certifiers(): void
    {
        $this->actingAs($this->manager)
            ->get(route('manager.certifiers'))
            ->assertOk();
    }

    public function test_manager_can_create_certifier(): void
    {
        $this->actingAs($this->manager)
            ->post(route('manager.certifiers.store'), [
                'firstname' => 'Maria',
                'lastname' => 'Garcia',
                'identification' => '987654321',
                'profession' => 'Doctora',
                'available' => '1',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('certifiers', ['identification' => '987654321']);
    }

    public function test_manager_can_update_certifier(): void
    {
        $certifier = $this->createCertifier();

        $this->actingAs($this->manager)
            ->post(route('manager.certifiers.update'), [
                'slack' => $certifier->slack,
                'firstname' => 'Carlos',
                'lastname' => 'Lopez',
                'identification' => $certifier->identification,
                'profession' => 'Abogado',
                'available' => '1',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_manager_can_delete_certifier(): void
    {
        $certifier = $this->createCertifier();

        $this->actingAs($this->manager)
            ->delete(route('manager.certifiers.destroy', $certifier->slack))
            ->assertRedirect(route('manager.certifiers'));

        $this->assertSoftDeleted('certifiers', ['id' => $certifier->id]);
    }

    public function test_unauthorized_user_cannot_write_certifiers(): void
    {
        // A manager user whose certifiers.create permission is revoked gets a 403
        // from EnforcePanelPermission.
        $restrictedManager = User::factory()->manager()->create();
        $restrictedManager->syncRoles([]);

        $this->actingAs($restrictedManager)
            ->post(route('manager.certifiers.store'), [
                'firstname' => 'Intento',
                'lastname' => 'NoAutorizado',
                'identification' => '000000000',
                'profession' => 'Ninguna',
                'available' => '1',
            ])
            ->assertForbidden();
    }
}
