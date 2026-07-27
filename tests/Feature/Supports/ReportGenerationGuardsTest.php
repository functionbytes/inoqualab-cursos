<?php

namespace Tests\Feature\Supports;

use App\Models\Distributor\Distributor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Las URLs de generación de informes son GET, así que se llega a ellas sin
 * parámetros con solo pegar el enlace. Los controllers hacían
 * `explode(' - ', $request->range)` y accedían a `$date[1]` a pelo, lo que
 * producía "Undefined array key 1" y un 500 en blanco.
 *
 * Ahora `parse_date_range()` devuelve null y el controller responde con un
 * mensaje. Un caso aparte era CoursesExport, cuyo `query()` devolvía null si la
 * modalidad no era 0/1/2: FromQuery lo clonaba y saltaba
 * "__clone method called on non-object".
 */
class ReportGenerationGuardsTest extends TestCase
{
    use RefreshDatabase;

    protected User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->support = User::factory()->create(['role' => 'support']);
        $this->support->syncRoles(['support']);
    }

    public static function reportRoutes(): array
    {
        return [
            'órdenes de distribuidores' => ['support.distributors.orders.generate'],
            'facturas de distribuidores' => ['support.distributors.invoices.generate'],
            'ingresos de usuarios de empresa' => ['support.enterprises.users.incoming'],
        ];
    }

    #[DataProvider('reportRoutes')]
    public function test_report_without_range_does_not_500(string $routeName): void
    {
        $this->actingAs($this->support)
            ->get(route($routeName))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    #[DataProvider('reportRoutes')]
    public function test_report_with_malformed_range_does_not_500(string $routeName): void
    {
        $this->actingAs($this->support)
            ->get(route($routeName, ['range' => 'no-es-un-rango']))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_staff_report_without_range_does_not_500(): void
    {
        // Este informe resuelve primero el distribuidor (404 si no existe), así
        // que hay que darle uno real para llegar al guard del rango.
        $distributor = Distributor::factory()->create();

        $this->actingAs($this->support)
            ->get(route('support.distributors.staffs.reports.generate', ['distributor' => $distributor->slack]))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_inscription_action_rejects_malformed_range_without_500(): void
    {
        // Este endpoint parsea con createFromFormat('d/m/Y') porque su picker
        // emite DD/MM/YYYY: NO puede usar parse_date_range (Carbon::parse leería
        // 05/03/2026 como mayo). El guard es propio y responde 422.
        $this->actingAs($this->support)
            ->postJson(route('support.users.inscriptions.action'), [
                'inscription' => 'lo-que-sea',
                'range' => 'fecha-suelta',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_course_report_without_modality_does_not_500(): void
    {
        // CoursesExport ya no devuelve null: cae en la modalidad "todos".
        $this->actingAs($this->support)
            ->get(route('support.enterprises.courses.generate'))
            ->assertOk();
    }
}
