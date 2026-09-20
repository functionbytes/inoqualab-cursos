<?php

namespace Tests\Feature\Supports\Distributors;

use App\Models\Distributor\Distributor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->support = User::factory()->support()->create();
    }

    // ── Bug: el input oculto "distributor" no tenia id ──────────────────
    // El JS de la vista lee $("#distributor").val() para armar la URL del
    // reporte; sin el atributo id, .val() devolvia undefined, el
    // querystring generado llevaba "distributor=" vacio, y como
    // OrdersExport compara con "!= 0" (comparacion debil de PHP,
    // null/'' != 0 es false), el filtro por distribuidor NUNCA se
    // aplicaba: el reporte de UN distribuidor exportaba las ordenes de
    // TODO el sistema.

    public function test_report_form_exposes_the_distributor_id_to_the_report_js(): void
    {
        $distributor = Distributor::factory()->create();

        $response = $this->actingAs($this->support)
            ->get(route('support.distributors.orders.reports', $distributor->slack));

        $response->assertOk()
            ->assertSee('id="distributor" name="distributor" value="'.$distributor->id.'"', false);
    }
}
