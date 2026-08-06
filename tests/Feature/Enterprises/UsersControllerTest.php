<?php

namespace Tests\Feature\Enterprises;

use App\Models\Enterprise\Enterprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * El portal enterprise no tenía NINGÚN test dedicado (solo cobertura indirecta
 * vía PortalRoutesSmokeTest). Regresión de UsersController::index(): cualquier
 * búsqueda con un guion que no fuera una fecha ISO ("López-Restrepo", apellido
 * compuesto) tiraba 500 -- Carbon::createFromFormat('Y-m-d', ...) lanza
 * InvalidArgumentException sobre texto que no es fecha.
 */
class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $enterpriseStaff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $enterprise = Enterprise::factory()->create();
        $this->enterpriseStaff = User::factory()->create(['role' => 'enterprise']);
        DB::table('enterprise_staff')->insert([
            'enterprise_id' => $enterprise->id,
            'user_id' => $this->enterpriseStaff->id,
        ]);
    }

    public function test_search_with_a_hyphen_that_is_not_a_date_does_not_500(): void
    {
        // Antes: Carbon::createFromFormat('Y-m-d', 'López-Restrepo') lanzaba
        // InvalidArgumentException sin capturar.
        $this->actingAs($this->enterpriseStaff)
            ->get(route('enterprise.users', ['search' => 'López-Restrepo']))
            ->assertOk();
    }

    public function test_search_with_a_valid_date_range_filters_by_updated_at(): void
    {
        $this->actingAs($this->enterpriseStaff)
            ->get(route('enterprise.users', ['search' => '2026-01-15']))
            ->assertOk();
    }

    public function test_search_without_hyphen_filters_by_text(): void
    {
        $this->actingAs($this->enterpriseStaff)
            ->get(route('enterprise.users', ['search' => 'Ana']))
            ->assertOk();
    }

    // ── Regresión: update() ponía available=null en cualquier guardado ──────
    // ── porque edit.blade.php no tiene ningún campo `available` ─────────────

    public function test_update_does_not_clear_available_when_the_field_is_not_sent(): void
    {
        $employee = User::factory()->create(['role' => 'customer', 'available' => 1]);
        DB::table('enterprise_user')->insert([
            'enterprise_id' => DB::table('enterprise_staff')->where('user_id', $this->enterpriseStaff->id)->value('enterprise_id'),
            'user_id' => $employee->id,
        ]);

        $this->actingAs($this->enterpriseStaff)
            ->postJson(route('enterprise.users.update'), [
                'slack' => $employee->slack,
                'firstname' => $employee->firstname,
                'lastname' => $employee->lastname,
                'email' => $employee->email,
                // available deliberadamente ausente, como manda el form real.
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(1, (int) $employee->fresh()->available);
    }

    public function test_update_sets_available_when_the_field_is_explicitly_sent(): void
    {
        $employee = User::factory()->create(['role' => 'customer', 'available' => 1]);
        DB::table('enterprise_user')->insert([
            'enterprise_id' => DB::table('enterprise_staff')->where('user_id', $this->enterpriseStaff->id)->value('enterprise_id'),
            'user_id' => $employee->id,
        ]);

        $this->actingAs($this->enterpriseStaff)
            ->postJson(route('enterprise.users.update'), [
                'slack' => $employee->slack,
                'firstname' => $employee->firstname,
                'lastname' => $employee->lastname,
                'email' => $employee->email,
                'available' => 0,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(0, (int) $employee->fresh()->available);
    }
}
