<?php

namespace Tests\Unit\Exports;

use App\Exports\Distributors\IncomesExport;
use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\Order\OrderMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresión: Distributors\IncomesExport y Supports\IncomesExport resolvían
 * método de pago/curso/distribuidor/empresa con una consulta POR FILA
 * dentro de map() (Course::id(...)->title, etc.), a diferencia de su
 * hermana Managers\IncomesExport, que ya precarga esos catálogos en el
 * constructor. Un reporte de ingresos con miles de filas disparaba miles de
 * queries extra en una sola descarga.
 */
class IncomesExportQueryEfficiencyTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Course, 1: Distributor, 2: Enterprise, 3: OrderMethod} */
    private function seedCatalogs(): array
    {
        $course = Course::factory()->create();
        $distributor = Distributor::factory()->create();
        $enterprise = Enterprise::factory()->create();
        $method = OrderMethod::firstOrCreate(['slug' => 'card'], ['slack' => 'om-card', 'title' => 'Tarjeta', 'slug' => 'card']);

        return [$course, $distributor, $enterprise, $method];
    }

    private function fakeRow($course, $distributor, $enterprise, $method): \stdClass
    {
        $row = new \stdClass;
        $row->order_slack = 'ord-1';
        $row->order_number = 1;
        $row->inscription_enroll_expire = now();
        $row->inscription_enroll_culminated = null;
        $row->order_payment_at = now();
        $row->order_method = $method->id;
        $row->inscription_course = $course->id;
        $row->orders_activity_distributor = $distributor->id;
        $row->orders_activity_enterprise = $enterprise->id;
        $row->firstname = 'Ana';
        $row->lastname = 'Gómez';
        $row->identification = '123';

        return $row;
    }

    public function test_distributors_incomes_export_map_does_not_query_per_row(): void
    {
        [$course, $distributor, $enterprise, $method] = $this->seedCatalogs();
        $export = new IncomesExport($enterprise->id, 0, now()->subMonth(), now());

        $rows = [
            $this->fakeRow($course, $distributor, $enterprise, $method),
            $this->fakeRow($course, $distributor, $enterprise, $method),
            $this->fakeRow($course, $distributor, $enterprise, $method),
        ];

        DB::flushQueryLog();
        DB::enableQueryLog();
        foreach ($rows as $row) {
            $export->map($row);
        }
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(0, $queries, "map() de Distributors\\IncomesExport disparó {$queries} queries para 3 filas -- debería precargar los catálogos y no consultar nada aquí.");
    }

    public function test_supports_incomes_export_map_does_not_query_per_row(): void
    {
        [$course, $distributor, $enterprise, $method] = $this->seedCatalogs();
        $export = new \App\Exports\Supports\IncomesExport($enterprise->id, 0, now()->subMonth(), now());

        $rows = [
            $this->fakeRow($course, $distributor, $enterprise, $method),
            $this->fakeRow($course, $distributor, $enterprise, $method),
        ];

        DB::flushQueryLog();
        DB::enableQueryLog();
        foreach ($rows as $row) {
            $export->map($row);
        }
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(0, $queries, "map() de Supports\\IncomesExport disparó {$queries} queries para 2 filas -- debería precargar los catálogos y no consultar nada aquí.");
    }
}
