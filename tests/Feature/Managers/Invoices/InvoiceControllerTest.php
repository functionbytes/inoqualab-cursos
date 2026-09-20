<?php

namespace Tests\Feature\Managers\Invoices;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceItem;
use App\Models\Invoice\InvoiceMethod;
use App\Models\Order\Order;
use App\Models\Order\OrderActivity;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
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
        InvoiceMethod::firstOrCreate(['slug' => 'transfer'], [
            'slack' => 'im-transfer',
            'title' => 'Transferencia',
            'slug' => 'transfer',
        ]);

        // 'id' no esta en $fillable de InvoiceCondition, asi que firstOrCreate()
        // lo ignoraria silenciosamente -- forceCreate() para fijar el id exacto.
        // InvoicesController::update() compara contra el literal 4 ("Pagada"),
        // no contra el slug, por eso necesitamos controlar el id en el test.
        if (! InvoiceCondition::find(1)) {
            InvoiceCondition::forceCreate([
                'id' => 1,
                'slack' => 'ic-generada',
                'title' => 'Generada',
                'slug' => 'generada',
            ]);
        }

        if (! InvoiceCondition::find(4)) {
            InvoiceCondition::forceCreate([
                'id' => 4,
                'slack' => 'ic-pagada',
                'title' => 'Pagada',
                'slug' => 'pagada',
            ]);
        }

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

        OrderCondition::firstOrCreate(['slug' => 'payment'], [
            'slack' => 'oc-payment',
            'title' => 'Pagada',
            'slug' => 'payment',
        ]);
    }

    private function makeDistributor(): Distributor
    {
        return Distributor::factory()->create();
    }

    private function makeInvoice(Distributor $distributor, int $conditionId): Invoice
    {
        $method = InvoiceMethod::where('slug', 'transfer')->first();

        return Invoice::create([
            'slack' => 'inv-'.uniqid(),
            'number' => (Invoice::max('number') ?? 0) + 1,
            'reference' => 'INV-'.uniqid(),
            'distributor_id' => $distributor->id,
            'method_id' => $method->id,
            'condition_id' => $conditionId,
            'total_discount_amount' => 0,
            'total_after_discount' => 100000,
            'total_before_discount' => 100000,
            'total_tax_amount' => 0,
            'total_invoices_amount' => 100000,
            'available' => 1,
            'notes' => '',
        ]);
    }

    // ── Bug #2: desglose de un bundle (quantity > 1) ─────────────────────
    // Antes del fix, InvoicesController::store() multiplicaba dos veces la
    // cantidad (amount * quantity, cuando amount ya era el total de linea),
    // inflando el desglose de invoice_items para paquetes con quantity > 1.

    public function test_invoice_breakdown_sums_to_header_total_for_bundle_with_quantity_greater_than_one(): void
    {
        Event::fake();

        $distributor = $this->makeDistributor();
        $customer = User::factory()->create(['role' => 'customer', 'available' => 1]);
        $course = Course::factory()->create();

        // Bundle: precio unitario 20000, cantidad 3 -> amount (total de linea) = 60000.
        $unitPrice = 20000;
        $quantity = 3;
        $lineAmount = $unitPrice * $quantity;

        $order = Order::create([
            'slack' => 'ord-'.uniqid(),
            'number' => (Order::max('number') ?? 0) + 1,
            'reference' => 'ORD-'.uniqid(),
            'user_id' => $customer->id,
            'type_id' => OrderType::where('slug', 'online')->first()->id,
            'method_id' => OrderMethod::where('slug', 'card')->first()->id,
            'condition_id' => OrderCondition::where('slug', 'payment')->first()->id,
            'total_before_discount' => $lineAmount,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => $lineAmount,
        ]);

        OrderItem::create([
            'slack' => 'oi-'.uniqid(),
            'order_id' => $order->id,
            'item_id' => $course->id,
            'item_type' => Course::class,
            'quantity' => $quantity,
            'amount' => $lineAmount,
        ]);

        $enterprise = new Enterprise;
        $enterprise->save();

        OrderActivity::create([
            'slack' => 'oa-'.uniqid(),
            'order_id' => $order->id,
            'distributor_id' => $distributor->id,
            'course_id' => $course->id,
            'enterprise_id' => $enterprise->id,
            'item_type' => Course::class,
            'id_type' => 0,
            'relation_id' => 0,
            'invoiced' => 0,
            'created_at' => Carbon::now(),
        ]);

        $method = InvoiceMethod::where('slug', 'transfer')->first();
        $condition = InvoiceCondition::where('id', 1)->first();

        $start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end = Carbon::now()->endOfMonth()->format('Y-m-d');

        $response = $this->actingAs($this->manager)
            ->postJson(route('manager.invoices.store'), [
                'distributor' => $distributor->slack,
                'range' => "{$start} - {$end}",
                'methods' => $method->id,
                'condition' => $condition->id,
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $invoice = Invoice::where('slack', $response->json('data'))->firstOrFail();

        // El total de cabecera es correcto (viene de order->total_order_amount).
        $this->assertEquals($lineAmount, (float) $invoice->total_invoices_amount);

        // El desglose (invoice_items) debe sumar EXACTAMENTE el total de cabecera.
        $breakdownTotal = InvoiceItem::where('invoice_id', $invoice->id)->sum('total');

        $this->assertEquals($lineAmount, (float) $breakdownTotal);
        $this->assertEquals((float) $invoice->total_invoices_amount, (float) $breakdownTotal);

        $item = InvoiceItem::where('invoice_id', $invoice->id)->where('course_id', $course->id)->first();
        $this->assertEquals($quantity, $item->quantity);
        $this->assertEquals($lineAmount, (float) $item->total);

        // Antes del fix esto habria dado 60000 * 3 = 180000.
        $this->assertNotEquals($lineAmount * $quantity, (float) $item->total);
    }

    // ── #1: autorizacion explicita en metodos de solo lectura ───────────

    public function test_manager_without_invoices_permission_cannot_view_invoice(): void
    {
        $distributor = $this->makeDistributor();
        $invoice = $this->makeInvoice($distributor, 1);

        $this->manager->syncRoles([]);

        $this->actingAs($this->manager)
            ->get(route('manager.invoices.view', $invoice->slack))
            ->assertForbidden();
    }

    public function test_manager_without_invoices_permission_cannot_edit_invoice(): void
    {
        $distributor = $this->makeDistributor();
        $invoice = $this->makeInvoice($distributor, 1);

        $this->manager->syncRoles([]);

        $this->actingAs($this->manager)
            ->get(route('manager.invoices.edit', $invoice->slack))
            ->assertForbidden();
    }

    // ── #4: payment_at coherente con la condicion ───────────────────────

    public function test_updating_invoice_to_paid_condition_sets_payment_at(): void
    {
        $distributor = $this->makeDistributor();
        $invoice = $this->makeInvoice($distributor, 1);
        $method = InvoiceMethod::where('slug', 'transfer')->first();

        $this->actingAs($this->manager)
            ->postJson(route('manager.invoices.update'), [
                'slack' => $invoice->slack,
                'condition' => 4,
                'methods' => $method->id,
                'payment' => '2026-01-15',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $invoice->refresh();
        $this->assertEquals(4, $invoice->condition_id);
        $this->assertNotNull($invoice->payment_at);
        $this->assertEquals('2026-01-15', Carbon::parse($invoice->payment_at)->format('Y-m-d'));
    }

    public function test_updating_invoice_away_from_paid_condition_clears_payment_at(): void
    {
        $distributor = $this->makeDistributor();
        $invoice = $this->makeInvoice($distributor, 4);
        $invoice->payment_at = Carbon::parse('2026-01-01');
        $invoice->save();

        $method = InvoiceMethod::where('slug', 'transfer')->first();

        $this->actingAs($this->manager)
            ->postJson(route('manager.invoices.update'), [
                'slack' => $invoice->slack,
                'condition' => 1,
                'methods' => $method->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $invoice->refresh();
        $this->assertEquals(1, $invoice->condition_id);
        $this->assertNull($invoice->payment_at);
    }

    // ── Bug: la vista "Detalle factura" leia columnas inexistentes ──────
    // ($invoice->date/enroll_start/enroll_expire no existen en la tabla
    // invoices -- son from_at/to_at/payment_at/created_at -- asi que
    // siempre mostraba 1969-12-31, el epoch de strtotime(null)).

    public function test_view_shows_real_dates_not_the_1969_epoch_bug(): void
    {
        $distributor = $this->makeDistributor();
        $invoice = $this->makeInvoice($distributor, 1);
        $invoice->from_at = '2025-05-01';
        $invoice->to_at = '2025-06-01';
        $invoice->save();

        $response = $this->actingAs($this->manager)
            ->get(route('manager.invoices.view', $invoice->slack));

        $response->assertOk()
            ->assertSee($invoice->created_at->format('Y-m-d'))
            ->assertSee('2025-05-01')
            ->assertSee('2025-06-01')
            ->assertDontSee('1969-12-31');
    }
}
