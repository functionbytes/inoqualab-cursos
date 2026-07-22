<?php

namespace Tests\Feature\Managers\Users;

use App\Enums\OrderCondition as Condition;
use App\Models\Order\Order;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Blinda el fix de destroyOrders (Managers): exige el permiso orders.delete y NO
 * permite borrar una orden pagada (evita el borrado en cascada de matrículas y
 * certificados del alumno).
 */
class UserOrdersDestroyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function seedLookups(): void
    {
        OrderType::firstOrCreate(['slug' => 'online'], ['slack' => 'ot', 'title' => 'Online', 'slug' => 'online']);
        OrderMethod::firstOrCreate(['slug' => 'card'], ['slack' => 'om', 'title' => 'Tarjeta', 'slug' => 'card']);
        foreach ([1 => 'generada', 2 => 'pendiente', 3 => 'rechazada', 4 => 'payment'] as $id => $slug) {
            \DB::table('order_condition')->insertOrIgnore(['id' => $id, 'slack' => 'oc-'.$slug, 'title' => ucfirst($slug), 'slug' => $slug]);
        }
    }

    private function makeOrder(User $customer, int $conditionId): Order
    {
        return Order::create([
            'slack' => Str::random(6),
            'number' => random_int(1000, 999999),
            'reference' => 'FAC'.random_int(1000, 999999),
            'user_id' => $customer->id,
            'type_id' => OrderType::where('slug', 'online')->first()->id,
            'method_id' => OrderMethod::where('slug', 'card')->first()->id,
            'condition_id' => $conditionId,
            'total_before_discount' => 50000,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => 50000,
        ]);
    }

    public function test_manager_can_delete_unpaid_order(): void
    {
        $this->seedLookups();
        $customer = User::factory()->customer()->create();
        $order = $this->makeOrder($customer, Condition::Generada->value);
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->delete(route('manager.enterprises.users.orders.destroy', $order->slack))
            ->assertRedirect();

        $this->assertNull(Order::find($order->id));
    }

    public function test_manager_cannot_delete_paid_order(): void
    {
        $this->seedLookups();
        $customer = User::factory()->customer()->create();
        $order = $this->makeOrder($customer, Condition::Pagada->value);
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->delete(route('manager.enterprises.users.orders.destroy', $order->slack))
            ->assertSessionHas('error');

        // La orden pagada NO se borra (evita cascada a inscripciones/certificados).
        $this->assertNotNull(Order::find($order->id));
    }

    public function test_delete_forbidden_without_orders_delete_permission(): void
    {
        $this->seedLookups();
        $customer = User::factory()->customer()->create();
        $order = $this->makeOrder($customer, Condition::Generada->value);

        Role::findByName('manager')->revokePermissionTo('orders.delete');
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->delete(route('manager.enterprises.users.orders.destroy', $order->slack))
            ->assertForbidden();

        $this->assertNotNull(Order::find($order->id));
    }
}
