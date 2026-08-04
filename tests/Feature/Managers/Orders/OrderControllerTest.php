<?php

namespace Tests\Feature\Managers\Orders;

use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'available' => 1,
            'validation' => 1,
        ]);

        $this->seedLookups();
    }

    // ── Seed helpers ──────────────────────────────────────────────────────

    private function seedLookups(): void
    {
        OrderType::firstOrCreate(['slug' => 'online'], [
            'slack' => 'ot-online',
            'title' => 'Online',
            'slug' => 'online',
        ]);

        OrderMethod::firstOrCreate(['slug' => 'card'], [
            'slack' => 'om-card',
            'title' => 'Tarjeta',
            'slug' => 'card',
        ]);

        // 'id' no esta en $fillable de OrderCondition, asi que firstOrCreate()
        // lo ignoraria silenciosamente -- forceCreate() para fijar el id exacto.
        // OrdersController::update() compara contra el literal 4 ("Pagada"),
        // no contra el slug, por eso necesitamos controlar el id en el test.
        if (! OrderCondition::find(1)) {
            OrderCondition::forceCreate([
                'id' => 1,
                'slack' => 'oc-pendiente',
                'title' => 'Pendiente',
                'slug' => 'pendiente',
            ]);
        }

        if (! OrderCondition::find(4)) {
            OrderCondition::forceCreate([
                'id' => 4,
                'slack' => 'oc-pagada',
                'title' => 'Pagada',
                'slug' => 'payment',
            ]);
        }
    }

    private function makeOrder(int $conditionId): Order
    {
        $customer = User::factory()->create(['role' => 'customer', 'available' => 1]);

        return Order::create([
            'slack' => 'ord-'.uniqid(),
            'number' => (Order::max('number') ?? 0) + 1,
            'reference' => 'ORD-'.uniqid(),
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

    // ── #1: autorizacion explicita en metodos de solo lectura ───────────

    public function test_manager_without_orders_permission_cannot_view_order(): void
    {
        $order = $this->makeOrder(1);

        $this->manager->syncRoles([]);

        $this->actingAs($this->manager)
            ->get(route('manager.orders.view', $order->slack))
            ->assertForbidden();
    }

    public function test_manager_without_orders_permission_cannot_edit_order(): void
    {
        $order = $this->makeOrder(1);

        $this->manager->syncRoles([]);

        $this->actingAs($this->manager)
            ->get(route('manager.orders.edit', $order->slack))
            ->assertForbidden();
    }

    // ── #4: payment_at coherente con la condicion ───────────────────────

    public function test_updating_order_to_paid_condition_sets_payment_at(): void
    {
        $order = $this->makeOrder(1);
        $method = OrderMethod::where('slug', 'card')->first();

        $this->actingAs($this->manager)
            ->postJson(route('manager.orders.update'), [
                'slack' => $order->slack,
                'condition' => 4,
                'methods' => $method->id,
                'payment' => '2026-02-10',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $order->refresh();
        $this->assertEquals(4, $order->condition_id);
        $this->assertNotNull($order->payment_at);
        $this->assertEquals('2026-02-10', Carbon::parse($order->payment_at)->format('Y-m-d'));
    }

    public function test_updating_order_away_from_paid_condition_clears_payment_at(): void
    {
        $order = $this->makeOrder(4);
        $order->payment_at = Carbon::parse('2026-01-01');
        $order->save();

        $method = OrderMethod::where('slug', 'card')->first();

        $this->actingAs($this->manager)
            ->postJson(route('manager.orders.update'), [
                'slack' => $order->slack,
                'condition' => 1,
                'methods' => $method->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $order->refresh();
        $this->assertEquals(1, $order->condition_id);
        $this->assertNull($order->payment_at);
    }

    // ── destroy(): nombre de ruta correcto + cliente soft-eliminado ─────

    public function test_destroy_redirects_to_manager_users_orders(): void
    {
        $order = $this->makeOrder(1);

        // Antes usaba route('managers.users.orders', ...) -- "managers" en
        // plural no existe (la convención real es manager.*, singular) y
        // esto lanzaba RouteNotFoundException en cualquier borrado exitoso.
        $this->actingAs($this->manager)
            ->delete(route('manager.orders.destroy', $order->slack))
            ->assertRedirect(route('manager.users.orders', $order->user->slack));

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    public function test_destroy_redirects_safely_when_customer_was_soft_deleted(): void
    {
        $order = $this->makeOrder(1);
        $order->user->delete(); // soft delete

        // Sin el guard, $order->user->slack sobre null revienta con 500.
        $this->actingAs($this->manager)
            ->delete(route('manager.orders.destroy', $order->slack))
            ->assertRedirect(route('manager.users'));

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }
}
