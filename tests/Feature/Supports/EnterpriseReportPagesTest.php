<?php

namespace Tests\Feature\Supports;

use App\Models\Enterprise\Enterprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Las pantallas de informe de empresa montan su formulario con
 * `Form::open(['route' => ['enterprises.users.generate']])`, en plural, pero la
 * ruta registrada es `enterprise.users.generate`, en singular. Blade resuelve
 * la ruta al renderizar, así que la página entera moría con
 * `RouteNotFoundException` antes de pintar nada.
 *
 * El mismo error de nombre está repetido en 12 vistas de cinco portales
 * (support, accounting, manager, distributor y enterprise).
 */
class EnterpriseReportPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $support;

    protected Enterprise $enterprise;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->support = User::factory()->create(['role' => 'support']);
        $this->support->syncRoles(['support']);
        $this->enterprise = Enterprise::factory()->create();
    }

    public function test_users_report_page_renders(): void
    {
        $this->actingAs($this->support)
            ->get(route('support.enterprises.users.reports', $this->enterprise->slack))
            ->assertOk();
    }
}
