<?php

namespace Tests\Feature\Managers\Invoices;

use App\Models\Distributor\Distributor;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceMethod;
use App\Models\Order\Order;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Diseños de orden / factura / reparto (original, a Comprobante, b Mesa de
 * trabajo, c Ficha técnica): el de por defecto se elige en Configuración de
 * facturación (panel_documents_design) y ?diseno= lo cambia solo en pantalla.
 */
class DocumentDesignTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->manager = User::factory()->create(['role' => 'manager', 'available' => 1, 'validation' => 1]);

        InvoiceMethod::firstOrCreate(['slug' => 'transfer'], ['slack' => 'im-transfer', 'title' => 'Transferencia', 'slug' => 'transfer']);
        InvoiceCondition::forceCreate(['id' => 1, 'slack' => 'ic-generada', 'title' => 'Generada', 'slug' => 'generada']);
        OrderType::firstOrCreate(['slug' => 'online'], ['slack' => 'ot-online', 'title' => 'Online', 'slug' => 'online']);
        OrderMethod::firstOrCreate(['slug' => 'card'], ['slack' => 'om-card', 'title' => 'Tarjeta', 'slug' => 'card']);
        OrderCondition::forceCreate(['id' => 4, 'slack' => 'oc-pagada', 'title' => 'Pagada', 'slug' => 'payment']);
    }

    private function makeInvoice(): Invoice
    {
        return Invoice::create([
            'slack' => 'inv-'.uniqid(),
            'number' => 1,
            'reference' => 'FAC-TEST',
            'distributor_id' => Distributor::factory()->create()->id,
            'method_id' => InvoiceMethod::first()->id,
            'condition_id' => 1,
            'total_discount_amount' => 0,
            'total_after_discount' => 1250000,
            'total_before_discount' => 1250000,
            'total_tax_amount' => 0,
            'total_invoices_amount' => 1250000,
            'available' => 1,
            'notes' => '',
        ]);
    }

    private function makeOrder(): Order
    {
        return Order::create([
            'slack' => 'ord-'.uniqid(),
            'number' => 1,
            'reference' => 'ORD-TEST',
            'user_id' => User::factory()->create(['role' => 'customer', 'available' => 1])->id,
            'type_id' => OrderType::first()->id,
            'method_id' => OrderMethod::first()->id,
            'condition_id' => 4,
            'total_before_discount' => 61000,
            'total_discount_amount' => 11000,
            'total_tax_amount' => 0,
            'total_order_amount' => 50000,
            'payment_at' => now(),
        ]);
    }

    public function test_invoice_uses_the_comprobante_design_by_default(): void
    {
        $invoice = $this->makeInvoice();

        $this->actingAs($this->manager)
            ->get(route('manager.invoices.view', $invoice->slack))
            ->assertOk()
            ->assertViewIs('managers.views.documents.page')
            ->assertViewHas('kind', 'invoice')
            ->assertViewHas('design', 'a')
            ->assertSee('$ 1.250.000', false);
    }

    public function test_the_saved_setting_picks_the_design(): void
    {
        updateSettings(['panel_documents_design' => 'c']);
        $order = $this->makeOrder();

        $this->actingAs($this->manager)
            ->get(route('manager.orders.view', $order->slack))
            ->assertOk()
            ->assertViewIs('managers.views.documents.page')
            ->assertViewHas('kind', 'order')
            ->assertViewHas('design', 'c');
    }

    public function test_query_parameter_previews_another_design_without_saving_it(): void
    {
        updateSettings(['panel_documents_design' => 'c']);
        $invoice = $this->makeInvoice();

        $this->actingAs($this->manager)
            ->get(route('manager.invoices.details', [$invoice->slack, 'diseno' => 'b']))
            ->assertOk()
            ->assertViewIs('managers.views.documents.page')
            ->assertViewHas('kind', 'details')
            ->assertViewHas('design', 'b')
            ->assertSee('no hay reparto que mostrar');

        $this->assertSame('c', setting('panel_documents_design'));
    }

    public function test_original_design_is_still_reachable(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->manager)
            ->get(route('manager.orders.view', [$order->slack, 'diseno' => 'original']))
            ->assertOk()
            ->assertViewIs('managers.views.orders.orders.view');
    }

    public function test_unknown_design_falls_back_to_the_setting(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->manager)
            ->get(route('manager.orders.view', [$order->slack, 'diseno' => 'z']))
            ->assertOk()
            ->assertViewIs('managers.views.documents.page')
            ->assertViewHas('design', 'a');
    }

    public function test_settings_save_the_chosen_design(): void
    {
        $this->actingAs($this->manager)
            ->post(route('manager.settings.invoices.update'), [
                'invoice_default' => 'FAC',
                'invoice_days' => 30,
                'panel_documents_design' => 'b',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('settings', ['key' => 'panel_documents_design', 'value' => 'b']);
    }

    public function test_settings_reject_an_unknown_design(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.settings.invoices.update'), [
                'invoice_default' => 'FAC',
                'invoice_days' => 30,
                'panel_documents_design' => 'z',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('panel_documents_design');
    }
}
