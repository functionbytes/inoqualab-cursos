<?php

namespace Tests\Feature\Supports\Distributors;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceDetails;
use App\Models\Invoice\InvoiceItem;
use App\Models\Invoice\InvoiceMethod;
use App\Models\Order\Order;
use App\Models\User;
use Database\Seeders\CatalogsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CatalogsSeeder::class);

        $this->support = User::factory()->support()->create();

        InvoiceMethod::firstOrCreate(['slug' => 'transfer'], [
            'slack' => 'im-transfer',
            'title' => 'Transferencia',
            'slug' => 'transfer',
        ]);

        // 'id' no esta en $fillable de InvoiceCondition: forceCreate() para
        // fijar el id exacto (condition_id=1 usado en makeInvoice()).
        if (! InvoiceCondition::find(1)) {
            InvoiceCondition::forceCreate([
                'id' => 1,
                'slack' => 'ic-generada',
                'title' => 'Generada',
                'slug' => 'generada',
            ]);
        }
    }

    private function makeInvoice(Distributor $distributor): Invoice
    {
        $method = InvoiceMethod::where('slug', 'transfer')->first();

        return Invoice::create([
            'slack' => 'inv-'.uniqid(),
            'number' => (Invoice::max('number') ?? 0) + 1,
            'reference' => 'INV-'.uniqid(),
            'distributor_id' => $distributor->id,
            'method_id' => $method->id,
            'condition_id' => 1,
            'total_discount_amount' => 0,
            'total_after_discount' => 44000,
            'total_before_discount' => 44000,
            'total_tax_amount' => 0,
            'total_invoices_amount' => 44000,
            'available' => 1,
            'notes' => '',
            'from_at' => '2025-05-01',
            'to_at' => '2025-06-01',
        ]);
    }

    // ── Bug: la vista "Detalle factura" leia columnas inexistentes ──────
    // ($invoice->date/enroll_start/enroll_expire no existen en la tabla
    // invoices -- siempre mostraba 1969-12-31, el epoch de
    // strtotime(null)) y $item->amount (no existe en invoice_items,
    // la columna real es total) -- siempre mostraba $0 en la columna Total.

    public function test_view_shows_real_dates_and_totals_not_the_1969_epoch_bug(): void
    {
        $distributor = Distributor::factory()->create();
        $invoice = $this->makeInvoice($distributor);

        $course = Course::factory()->create();
        InvoiceItem::create([
            'slack' => 'item-'.uniqid(),
            'invoice_id' => $invoice->id,
            'course_id' => $course->id,
            'quantity' => 4,
            'subtotal' => 11000,
            'total' => 44000,
        ]);

        $response = $this->actingAs($this->support)
            ->get(route('support.distributors.invoices.view', $invoice->slack));

        $response->assertOk()
            ->assertSee($invoice->created_at->format('Y-m-d'))
            ->assertSee('2025-05-01')
            ->assertSee('2025-06-01')
            ->assertSee('44,000')
            ->assertDontSee('1969-12-31');
    }

    // ── Bug: detail() multiplicaba dos veces el total por curso ─────────
    // $amount ya es la suma de los totales de linea de cada InvoiceDetails
    // agrupado (no un precio unitario a multiplicar por quantity de nuevo).
    // Con 3 inscripciones reales del mismo curso (quantity=1, amount=11000
    // cada una): sum(quantity)=3, sum(amount)=33000. El bug mostraba
    // 3 * 33000 = 99000 en vez de 33000 (peor cuanto mas inscripciones:
    // una factura real con 204 inscripciones del mismo curso mostraba
    // $104.040.000 en vez de $510.000).

    public function test_detail_does_not_double_count_the_total_per_course(): void
    {
        $distributor = Distributor::factory()->create();
        $invoice = $this->makeInvoice($distributor);
        $enterprise = Enterprise::factory()->create();
        $course = Course::factory()->create();

        foreach (range(1, 3) as $i) {
            $order = Order::factory()->create();
            // forceCreate(): enterprise_id no esta en $fillable de InvoiceDetails.
            InvoiceDetails::forceCreate([
                'slack' => 'detail-'.uniqid().$i,
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
                'course_id' => $course->id,
                'enterprise_id' => $enterprise->id,
                'quantity' => 1,
                'amount' => 11000,
            ]);
        }

        $response = $this->actingAs($this->support)
            ->get(route('support.distributors.invoices.detail', $invoice->slack));

        $response->assertOk()
            ->assertSee('33,000')
            ->assertDontSee('99,000');
    }
}
