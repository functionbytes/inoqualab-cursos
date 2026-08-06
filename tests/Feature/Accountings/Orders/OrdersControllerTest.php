<?php

namespace Tests\Feature\Accountings\Orders;

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
 * Regresión: OrdersController::update() solo asignaba payment_at cuando la
 * condición era "Pagada" (4), pero nunca lo limpiaba al cambiar a cualquier
 * otra condición -- una orden revertida de "Pagada" a "Pendiente" quedaba con
 * una fecha de pago residual del estado anterior, y payment_at es la fuente
 * de verdad en los exports de ingresos/facturación y en filtros como
 * Pages/BundlesController::whereNotNull('payment_at'). Mismo bug ya
 * corregido en el dominio hermano Managers/Orders/OrdersController.
 */
class OrdersControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $accounting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->accounting = User::factory()->create([
            'role' => 'accounting',
            'available' => 1,
            'validation' => 1,
        ]);

        $this->seedLookups();
    }

    private function seedLookups(): void
    {
        OrderType::firstOrCreate(['slug' => 'online'], ['slack' => 'ot-online', 'title' => 'Online', 'slug' => 'online']);
        OrderMethod::firstOrCreate(['slug' => 'card'], ['slack' => 'om-card', 'title' => 'Tarjeta', 'slug' => 'card']);

        // OrdersController::update() compara contra el literal 4 ("Pagada"),
        // así que hay que controlar el id exacto.
        if (! OrderCondition::find(1)) {
            OrderCondition::forceCreate(['id' => 1, 'slack' => 'oc-pendiente', 'title' => 'Pendiente', 'slug' => 'pendiente']);
        }
        if (! OrderCondition::find(4)) {
            OrderCondition::forceCreate(['id' => 4, 'slack' => 'oc-pagada', 'title' => 'Pagada', 'slug' => 'payment']);
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

    public function test_updating_order_to_paid_condition_sets_payment_at(): void
    {
        $order = $this->makeOrder(1);
        $method = OrderMethod::where('slug', 'card')->first();

        $this->actingAs($this->accounting)
            ->postJson(route('accounting.orders.update'), [
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

        $this->actingAs($this->accounting)
            ->postJson(route('accounting.orders.update'), [
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
}
