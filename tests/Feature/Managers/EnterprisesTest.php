<?php

namespace Tests\Feature\Managers;

use App\Models\Enterprise\Enterprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterprisesTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    private function createEnterprise(array $overrides = []): Enterprise
    {
        // email y slug no están en $fillable, se asignan directamente.
        $data = array_merge([
            'slack' => (string) Str::uuid(),
            'title' => 'EMPRESA PRUEBA S.A.',
            'nit' => '900'.rand(100000, 999999).'-'.rand(0, 9),
            'available' => 1,
        ], $overrides);

        $enterprise = new Enterprise($data);
        $enterprise->slug = $overrides['slug'] ?? Str::slug($data['title'], '-');
        $enterprise->email = $overrides['email'] ?? fake()->unique()->safeEmail();
        $enterprise->save();

        return $enterprise;
    }

    public function test_manager_can_list_enterprises(): void
    {
        $this->actingAs($this->manager)
            ->get(route('manager.enterprises'))
            ->assertOk();
    }

    public function test_manager_can_create_enterprise(): void
    {
        $this->actingAs($this->manager)
            ->post(route('manager.enterprises.store'), [
                'title' => 'Nueva Empresa',
                'nit' => '900123456-1',
                'email' => 'nueva@empresa.com',
                'address' => 'Calle 1 # 2-3',
                'cellphone' => '3001234567',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('enterprises', ['email' => 'nueva@empresa.com']);
    }

    public function test_manager_can_update_enterprise(): void
    {
        $enterprise = $this->createEnterprise();

        $this->actingAs($this->manager)
            ->post(route('manager.enterprises.update'), [
                'slack' => $enterprise->slack,
                'title' => 'Empresa Actualizada',
                'nit' => $enterprise->nit,
                'email' => $enterprise->email,
                'address' => 'Carrera 5 # 10-20',
                'cellphone' => '3109876543',
                'available' => '1',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_store_rejects_duplicate_email(): void
    {
        $this->createEnterprise(['email' => 'duplicado@empresa.com']);

        $this->actingAs($this->manager)
            ->post(route('manager.enterprises.store'), [
                'title' => 'Otra Empresa',
                'nit' => '800000001-9',
                'email' => 'duplicado@empresa.com',
                'address' => 'Calle 1 # 2-3',
            ])
            ->assertOk()
            ->assertJson(['success' => false]);
    }

    public function test_unauthorized_user_cannot_create_enterprise(): void
    {
        // A manager user whose enterprises.create permission is revoked gets a 403
        // from EnforcePanelPermission.
        $restrictedManager = User::factory()->manager()->create();
        $restrictedManager->syncRoles([]);

        $this->actingAs($restrictedManager)
            ->post(route('manager.enterprises.store'), [
                'title' => 'Intento No Autorizado',
                'nit' => '111111111-1',
                'email' => 'hack@empresa.com',
            ])
            ->assertForbidden();
    }
}
