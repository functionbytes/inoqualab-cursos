<?php

namespace Tests\Feature\Distributors\Invoices;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceCondition;
use App\Models\Invoice\InvoiceDetails;
use App\Models\Invoice\InvoiceMethod;
use App\Models\Order\Order;
use App\Models\User;
use Database\Seeders\CatalogsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// ── Bug: detail() multiplicaba dos veces el total por curso ─────────────
// Mismo defecto que ya se corrigio en Supports\Distributors\InvoicesController:
// $amount ya es la suma de los totales de linea de cada InvoiceDetails
// agrupado (no un precio unitario a multiplicar de nuevo por quantity).
// Con datos reales (factura slack=Gzhf1f, 134 filas qty=1/amount=2500 c/u
// del mismo curso) el bug mostraba $44.890.000 en vez de $335.000 (134x).
class InvoicesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $distributorStaff;

    private Distributor $distributor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CatalogsSeeder::class);

        $this->distributor = Distributor::factory()->create();
        $this->distributorStaff = User::factory()->create(['role' => 'distributor']);
        DB::table('distributor_staff')->insert([
            'distributor_id' => $this->distributor->id,
            'user_id' => $this->distributorStaff->id,
        ]);

        InvoiceMethod::firstOrCreate(['slug' => 'transfer'], [
            'slack' => 'im-transfer',
            'title' => 'Transferencia',
            'slug' => 'transfer',
        ]);

        // 'id' no esta en $fillable de InvoiceCondition: forceCreate() para
        // fijar el id exacto (condition_id=1 usado abajo).
        if (! InvoiceCondition::find(1)) {
            InvoiceCondition::forceCreate([
                'id' => 1,
                'slack' => 'ic-generada',
                'title' => 'Generada',
                'slug' => 'generada',
            ]);
        }
    }

    public function test_detail_does_not_double_count_the_total_per_course(): void
    {
        $method = InvoiceMethod::where('slug', 'transfer')->first();

        $invoice = Invoice::create([
            'slack' => 'inv-'.uniqid(),
            'number' => (Invoice::max('number') ?? 0) + 1,
            'reference' => 'INV-'.uniqid(),
            'distributor_id' => $this->distributor->id,
            'method_id' => $method->id,
            'condition_id' => 1,
            'total_discount_amount' => 0,
            'total_after_discount' => 33000,
            'total_before_discount' => 33000,
            'total_tax_amount' => 0,
            'total_invoices_amount' => 33000,
            'available' => 1,
            'notes' => '',
            'from_at' => '2025-05-01',
            'to_at' => '2025-06-01',
        ]);

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

        $response = $this->actingAs($this->distributorStaff)
            ->get(route('distributor.invoices.detail', $invoice->slack));

        $response->assertOk()
            // Formato COP del diseño de documentos ($ 33.000, no 33,000).
            ->assertSee('$ 33.000', false)
            ->assertDontSee('$ 99.000', false);
    }
}
