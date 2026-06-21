<?php

namespace Tests\Feature\Console;

use App\Http\Controllers\Pages\CheckoutController;
use App\Mail\Customers\Orders\AbandonedOrderMail;
use App\Models\Order\Order;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use App\Services\WompiService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Cobertura de los comandos de consola sensibles de órdenes/pagos:
 * - orders:remind-abandoned (recordatorio único de pago)
 * - orders:reconcile-pending (reconsulta de estado a Wompi)
 */
class OrderCommandsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        OrderType::firstOrCreate(['slug' => 'online'], ['slack' => 'ot-online', 'title' => 'Online']);
        OrderMethod::firstOrCreate(['slug' => 'card'], ['slack' => 'om-card', 'title' => 'Tarjeta']);
        foreach ([1 => 'generada', 2 => 'pendiente', 3 => 'rechazada', 4 => 'payment'] as $id => $slug) {
            DB::table('order_condition')->insertOrIgnore(['id' => $id, 'slack' => 'oc-'.$slug, 'title' => ucfirst($slug), 'slug' => $slug]);
        }
    }

    private function makeOrder(array $overrides = [], ?string $createdAt = null, ?string $updatedAt = null): Order
    {
        $order = Order::create(array_merge([
            'slack' => 'ord-'.Str::random(8),
            'number' => random_int(1, 999999),
            'reference' => 'FAC'.Str::random(6),
            'user_id' => User::factory()->create()->id,
            'type_id' => OrderType::where('slug', 'online')->first()->id,
            'method_id' => OrderMethod::where('slug', 'card')->first()->id,
            'condition_id' => 1,
            'total_before_discount' => 50000,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => 50000,
        ], $overrides));

        // Ajustar timestamps sin disparar mutadores de Eloquent.
        if ($createdAt || $updatedAt) {
            DB::table('orders')->where('id', $order->id)->update(array_filter([
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]));
        }

        return $order->fresh();
    }

    // ── orders:remind-abandoned ─────────────────────────────────────────────

    public function test_reminds_abandoned_order_once(): void
    {
        Mail::fake();
        $order = $this->makeOrder(['condition_id' => 1], createdAt: now()->subDay()->toDateTimeString());

        $this->artisan('orders:remind-abandoned')->assertExitCode(0);

        Mail::assertQueued(AbandonedOrderMail::class);
        $this->assertNotNull($order->fresh()->reminded_at);
    }

    public function test_does_not_remind_when_already_reminded(): void
    {
        Mail::fake();
        $order = $this->makeOrder(['condition_id' => 1], createdAt: now()->subDay()->toDateTimeString());
        // reminded_at no está en $fillable: se fija directo en BD.
        DB::table('orders')->where('id', $order->id)->update(['reminded_at' => now()->subHour()]);

        $this->artisan('orders:remind-abandoned')->assertExitCode(0);

        Mail::assertNothingQueued();
    }

    // ── orders:reconcile-pending ────────────────────────────────────────────

    public function test_reconcile_skips_orders_still_pending_in_wompi(): void
    {
        $order = $this->makeOrder(
            ['condition_id' => 2, 'transaction' => 'txn-1'],
            createdAt: now()->subHour()->toDateTimeString(),
            updatedAt: now()->subMinutes(30)->toDateTimeString(),
        );

        $this->mock(WompiService::class)
            ->shouldReceive('getTransaction')->andReturn(['id' => 'txn-1', 'status' => 'PENDING']);
        // Sigue PENDING: no debe procesarse la orden.
        $this->mock(CheckoutController::class)
            ->shouldReceive('processOrderStatus')->never();

        $this->artisan('orders:reconcile-pending')->assertExitCode(0);

        $this->assertSame(2, $order->fresh()->condition_id);
    }

    public function test_reconcile_processes_orders_with_definitive_status(): void
    {
        $this->makeOrder(
            ['condition_id' => 2, 'transaction' => 'txn-2'],
            createdAt: now()->subHour()->toDateTimeString(),
            updatedAt: now()->subMinutes(30)->toDateTimeString(),
        );

        $this->mock(WompiService::class)
            ->shouldReceive('getTransaction')
            ->andReturn(['id' => 'txn-2', 'status' => 'APPROVED', 'amount_in_cents' => 5000000, 'currency' => 'COP', 'payment_method_type' => 'CARD']);
        // Estado definitivo: debe delegarse el procesamiento.
        $this->mock(CheckoutController::class)
            ->shouldReceive('processOrderStatus')->once();

        $this->artisan('orders:reconcile-pending')->assertExitCode(0);
    }
}
