<?php

namespace Tests\Feature\Accountings\Invoices;

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

/**
 * Regresión de la auditoría de facturas: Accountings\Invoices\InvoicesController
 * tenía el mismo bug de doble-multiplicación ya corregido en Managers\InvoicesController
 * (amount ya es el total de línea, no el precio unitario) y las mismas ramas
 * elseif vacías que dejaban payment_at con un valor obsoleto al cambiar de
 * condición "Pagada" a otra.
 */
class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $accountant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->accountant = User::factory()->create([
            'role' => 'accounting',
            'available' => 1,
            'validation' => 1,
        ]);

        $this->seedLookups();
    }

    private function seedLookups(): void
    {
        InvoiceMethod::firstOrCreate(['slug' => 'transfer'], [
            'slack' => 'im-transfer',
            'title' => 'Transferencia',
            'slug' => 'transfer',
        ]);

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

    // ── Bug: desglose de un bundle (quantity > 1) ───────────────────────────

    public function test_invoice_breakdown_sums_to_header_total_for_bundle_with_quantity_greater_than_one(): void
    {
        Event::fake();

        $distributor = Distributor::factory()->create();
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

        $response = $this->actingAs($this->accountant)
            ->postJson(route('accounting.invoices.store'), [
                'distributor' => $distributor->id,
                'range' => "{$start} - {$end}",
                'methods' => $method->id,
                'condition' => $condition->id,
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $invoice = Invoice::where('slack', $response->json('data'))->firstOrFail();

        $breakdownTotal = InvoiceItem::where('invoice_id', $invoice->id)->sum('total');

        $this->assertEquals($lineAmount, (float) $breakdownTotal);

        $item = InvoiceItem::where('invoice_id', $invoice->id)->where('course_id', $course->id)->first();
        $this->assertEquals($quantity, $item->quantity);
        $this->assertEquals($lineAmount, (float) $item->total);

        // Antes del fix esto habria dado 60000 * 3 = 180000.
        $this->assertNotEquals($lineAmount * $quantity, (float) $item->total);
    }

    // ── Bug: payment_at obsoleto al cambiar de condición ────────────────────

    public function test_updating_invoice_to_paid_condition_sets_payment_at(): void
    {
        $distributor = Distributor::factory()->create();
        $invoice = $this->makeInvoice($distributor, 1);
        $method = InvoiceMethod::where('slug', 'transfer')->first();

        $this->actingAs($this->accountant)->postJson(route('accounting.invoices.update'), [
            'slack' => $invoice->slack,
            'condition' => 4,
            'methods' => $method->id,
            'payment' => '2026-01-15',
        ])->assertOk();

        $invoice->refresh();
        $this->assertEquals(4, $invoice->condition_id);
        $this->assertEquals('2026-01-15', Carbon::parse($invoice->payment_at)->format('Y-m-d'));
    }

    public function test_updating_invoice_away_from_paid_condition_clears_payment_at(): void
    {
        $distributor = Distributor::factory()->create();
        $invoice = $this->makeInvoice($distributor, 4);
        $invoice->payment_at = Carbon::now();
        $invoice->save();

        $method = InvoiceMethod::where('slug', 'transfer')->first();

        $this->actingAs($this->accountant)->postJson(route('accounting.invoices.update'), [
            'slack' => $invoice->slack,
            'condition' => 1,
            'methods' => $method->id,
        ])->assertOk();

        $invoice->refresh();
        $this->assertEquals(1, $invoice->condition_id);
        $this->assertNull($invoice->payment_at);
    }
}
