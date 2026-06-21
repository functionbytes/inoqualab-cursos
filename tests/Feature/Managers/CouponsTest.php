<?php

namespace Tests\Feature\Managers;

use App\Models\Coupon\Coupon;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CouponsTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    private function createCoupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'slack' => (string) Str::uuid(),
            'title' => 'Cupón de prueba',
            'code' => 'TEST-'.strtoupper(Str::random(6)),
            'type' => 0,
            'amount' => 10,
            'available' => 1,
            'limit' => 0,
        ], $overrides));
    }

    public function test_manager_can_list_coupons(): void
    {
        $this->actingAs($this->manager)
            ->get(route('manager.coupons'))
            ->assertOk();
    }

    public function test_manager_can_create_coupon(): void
    {
        $this->actingAs($this->manager)
            ->post(route('manager.coupons.store'), [
                'title' => 'Cupón de descuento',
                'code' => 'PROMO2024',
                'type' => '0',
                'amount' => 15,
                'min_price' => 0,
                'available' => '1',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('coupons', ['code' => 'PROMO2024']);
    }

    public function test_manager_can_update_coupon(): void
    {
        $coupon = $this->createCoupon();

        $this->actingAs($this->manager)
            ->post(route('manager.coupons.update'), [
                'slack' => $coupon->slack,
                'title' => 'Cupón actualizado',
                'code' => $coupon->code,
                'type' => '0',
                'amount' => 20,
                'min_price' => 0,
                'available' => '1',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_manager_can_delete_coupon(): void
    {
        $coupon = $this->createCoupon();

        $this->actingAs($this->manager)
            ->delete(route('manager.coupons.destroy', $coupon->slack))
            ->assertRedirect(route('manager.coupons'));

        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    public function test_unauthorized_user_cannot_write_coupons(): void
    {
        // A manager user whose coupons.create permission is revoked gets a 403
        // from EnforcePanelPermission (the manager middleware lets them in, but
        // the permission middleware blocks the specific action).
        // Crear manager, remover el rol Spatie para que can('coupons.create') devuelva false.
        // revokePermissionTo() no basta porque el rol 'manager' sigue teniendo el permiso.
        $restrictedManager = User::factory()->manager()->create();
        $restrictedManager->syncRoles([]);

        $this->actingAs($restrictedManager)
            ->post(route('manager.coupons.store'), [
                'title' => 'Intento no autorizado',
                'code' => 'HACK123',
                'type' => '0',
                'amount' => 10,
                'min_price' => 0,
            ])
            ->assertForbidden();
    }
}
