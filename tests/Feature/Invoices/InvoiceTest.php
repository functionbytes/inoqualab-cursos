<?php

namespace Tests\Feature\Invoices;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceMethod;
use App\Models\Order\Order;
use App\Models\Order\OrderActivity;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderItem;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'available' => 1,
            'validation' => 1,
        ]);

        $this->seedInvoiceLookups();
    }

    // ── Seed helpers ──────────────────────────────────────────────────────────────

    private function seedInvoiceLookups(): void
    {
        InvoiceMethod::firstOrCreate(['slug' => 'transfer'], [
            'slack' => 'im-transfer',
            'title' => 'Transferencia',
            'slug' => 'transfer',
        ]);

        InvoiceCondition::firstOrCreate(['slug' => 'generada'], [
            'slack' => 'ic-generada',
            'title' => 'Generada',
            'slug' => 'generada',
        ]);

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

    /**
     * Create a paid order + OrderActivity tied to a distributor.
     * This is the data set InvoicesController::store() queries via
     * $distributor->orders()->date($start, $end).
     */
    private function makeDistributorOrder(Distributor $distributor): Order
    {
        $customer = User::factory()->create(['role' => 'customer', 'available' => 1]);
        $course = Course::factory()->create();

        $order = Order::create([
            'slack' => 'ord-'.uniqid(),
            'number' => (Order::max('number') ?? 0) + 1,
            'reference' => 'FAC'.(Order::max('number') ?? 0),
            'user_id' => $customer->id,
            'type_id' => OrderType::where('slug', 'online')->first()->id,
            'method_id' => OrderMethod::where('slug', 'card')->first()->id,
            'condition_id' => OrderCondition::where('slug', 'payment')->first()->id,
            'total_before_discount' => 50000,
            'total_discount_amount' => 0,
            'total_tax_amount' => 0,
            'total_order_amount' => 50000,
        ]);

        OrderItem::create([
            'slack' => 'oi-'.uniqid(),
            'order_id' => $order->id,
            'item_id' => $course->id,
            'item_type' => Course::class,
            'quantity' => 1,
            'amount' => 50000,
        ]);

        $enterprise = new Enterprise;
        $enterprise->save();

        // OrderActivity is what Distributor::orders() returns.
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

        return $order;
    }

    // ── Authorization ─────────────────────────────────────────────────────────────

    public function test_guest_is_redirected_from_invoices_index(): void
    {
        $this->get(route('manager.invoices'))->assertRedirect();
    }

    public function test_non_manager_cannot_access_invoices_index(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'available' => 1]);

        $this->actingAs($customer)
            ->get(route('manager.invoices'))
            ->assertRedirect();
    }

    // ── Happy path ────────────────────────────────────────────────────────────────

    public function test_manager_can_view_invoices_index(): void
    {
        $this->actingAs($this->manager)
            ->get(route('manager.invoices'))
            ->assertOk();
    }

    public function test_manager_can_create_invoice_for_distributor(): void
    {
        Event::fake();

        $distributor = $this->makeDistributor();
        $this->makeDistributorOrder($distributor);

        $method = InvoiceMethod::where('slug', 'transfer')->first();
        $condition = InvoiceCondition::where('slug', 'generada')->first();

        $start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end = Carbon::now()->endOfMonth()->format('Y-m-d');

        $response = $this->actingAs($this->manager)
            ->postJson(route('manager.invoices.store'), [
                'distributor' => $distributor->slack,
                'range' => "{$start} - {$end}",
                'methods' => $method->id,
                'condition' => $condition->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('invoices', [
            'distributor_id' => $distributor->id,
            'condition_id' => $condition->id,
        ]);
    }

    public function test_invoice_creation_fails_when_no_uninvoiced_orders_in_range(): void
    {
        $distributor = $this->makeDistributor();

        $method = InvoiceMethod::where('slug', 'transfer')->first();
        $condition = InvoiceCondition::where('slug', 'generada')->first();

        // Range is in the far past — no orders will match.
        $start = '2000-01-01';
        $end = '2000-01-31';

        $this->actingAs($this->manager)
            ->postJson(route('manager.invoices.store'), [
                'distributor' => $distributor->slack,
                'range' => "{$start} - {$end}",
                'methods' => $method->id,
                'condition' => $condition->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', false);
    }

    public function test_manager_can_update_invoice_condition(): void
    {
        $distributor = $this->makeDistributor();

        $method = InvoiceMethod::where('slug', 'transfer')->first();
        $generadaCondition = InvoiceCondition::where('slug', 'generada')->first();

        $invoice = Invoice::create([
            'slack' => 'inv-'.uniqid(),
            'number' => 1,
            'reference' => 'INV-001',
            'distributor_id' => $distributor->id,
            'method_id' => $method->id,
            'condition_id' => $generadaCondition->id,
            'total_discount_amount' => 0,
            'total_after_discount' => 100000,
            'total_before_discount' => 100000,
            'total_tax_amount' => 0,
            'total_invoices_amount' => 100000,
            'available' => 1,
            'notes' => '',
        ]);

        // Use a second condition (e.g. create a "pagada" condition) to update to.
        $paidCondition = InvoiceCondition::firstOrCreate(['slug' => 'pagada'], [
            'slack' => 'ic-pagada',
            'title' => 'Pagada',
            'slug' => 'pagada',
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.invoices.update'), [
                'slack' => $invoice->slack,
                'condition' => $paidCondition->id,
                'methods' => $method->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'condition_id' => $paidCondition->id,
        ]);
    }

    public function test_manager_can_view_invoice_edit_page(): void
    {
        $distributor = $this->makeDistributor();
        $method = InvoiceMethod::where('slug', 'transfer')->first();
        $condition = InvoiceCondition::where('slug', 'generada')->first();

        $invoice = Invoice::create([
            'slack' => 'inv-view-'.uniqid(),
            'number' => 2,
            'reference' => 'INV-002',
            'distributor_id' => $distributor->id,
            'method_id' => $method->id,
            'condition_id' => $condition->id,
            'total_discount_amount' => 0,
            'total_after_discount' => 50000,
            'total_before_discount' => 50000,
            'total_tax_amount' => 0,
            'total_invoices_amount' => 50000,
            'available' => 1,
            'notes' => '',
        ]);

        $this->actingAs($this->manager)
            ->get(route('manager.invoices.edit', $invoice->slack))
            ->assertOk();
    }

    public function test_manager_cannot_create_invoice_for_nonexistent_distributor(): void
    {
        $method = InvoiceMethod::where('slug', 'transfer')->first();
        $condition = InvoiceCondition::where('slug', 'generada')->first();

        // scopeSlack will abort 404 for an unknown distributor slack.
        $this->actingAs($this->manager)
            ->postJson(route('manager.invoices.store'), [
                'distributor' => 'does-not-exist',
                'range' => '2025-01-01 - 2025-01-31',
                'methods' => $method->id,
                'condition' => $condition->id,
            ])
            ->assertNotFound();
    }
}
