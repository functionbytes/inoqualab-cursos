<?php

namespace Tests\Feature\Supports\Users;

use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresion de la auditoria del dominio Usuarios (portal soporte):
 * - edit()/update() no existian en el controller aunque las rutas
 *   support.users.orders.edit / .update ya las referenciaban (500 al navegar).
 * - destroy() redirigia a 'manager.users.orders' (dominio equivocado) y
 *   accedia a $order->user->slack sin verificar null.
 */
class UsersOrdersControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->support = User::factory()->support()->create();

        $this->seedLookups();
    }

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

    private function makeOrder(int $conditionId, ?User $customer = null): Order
    {
        $customer ??= User::factory()->customer()->create();

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

    public function test_edit_page_loads_without_error(): void
    {
        $order = $this->makeOrder(1);

        $this->actingAs($this->support)
            ->get(route('support.users.orders.edit', $order->slack))
            ->assertOk()
            ->assertSee($order->slack);
    }

    public function test_update_sets_payment_at_when_condition_is_paid(): void
    {
        $order = $this->makeOrder(1);
        $method = OrderMethod::where('slug', 'card')->first();

        $this->actingAs($this->support)
            ->postJson(route('support.users.orders.update'), [
                'slack' => $order->slack,
                'condition' => 4,
                'methods' => $method->id,
                'payment' => '2026-02-10',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $order->refresh();
        $this->assertEquals(4, $order->condition_id);
        $this->assertEquals('2026-02-10', Carbon::parse($order->payment_at)->format('Y-m-d'));
    }

    public function test_update_clears_payment_at_when_condition_is_not_paid(): void
    {
        $order = $this->makeOrder(4);
        $order->payment_at = Carbon::parse('2026-01-01');
        $order->save();

        $method = OrderMethod::where('slug', 'card')->first();

        $this->actingAs($this->support)
            ->postJson(route('support.users.orders.update'), [
                'slack' => $order->slack,
                'condition' => 1,
                'methods' => $method->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $order->refresh();
        $this->assertNull($order->payment_at);
    }

    public function test_update_rejects_invalid_payload(): void
    {
        $order = $this->makeOrder(1);

        $this->actingAs($this->support)
            ->postJson(route('support.users.orders.update'), [
                'slack' => $order->slack,
                'condition' => 4,
                'methods' => null,
                // payment omitido a proposito: es requerido cuando condition=4
            ])
            ->assertUnprocessable();
    }

    public function test_destroy_redirects_to_support_orders_index_not_manager_domain(): void
    {
        $customer = User::factory()->customer()->create();
        $order = $this->makeOrder(1, $customer);

        $response = $this->actingAs($this->support)
            ->delete(route('support.users.orders.destroy', $order->slack));

        $response->assertRedirect(route('support.users.orders.index', $customer->slack));
        $this->assertNull(Order::find($order->id));
    }

    // Nota: `orders.user_id` tiene FK con ON DELETE CASCADE, por lo que en la
    // practica una orden nunca queda huerfana (borrar el usuario borra la
    // orden). El guard `$order->user?->slack` en destroy() se mantiene como
    // defensa adicional, pero no es reproducible como test de integracion
    // sin violar la constraint de la base de datos.

    public function test_destroy_refuses_to_delete_paid_order(): void
    {
        $customer = User::factory()->customer()->create();
        $order = $this->makeOrder(4, $customer);

        $response = $this->actingAs($this->support)
            ->delete(route('support.users.orders.destroy', $order->slack));

        // Las FK en cascada (orders → inscriptions → certificates) harian que
        // borrar una orden pagada arrastre matriculas y certificados del alumno.
        $response->assertRedirect(route('support.users.orders.index', $customer->slack))
            ->assertSessionHas('error');
        $this->assertNotNull(Order::find($order->id));
    }
}
