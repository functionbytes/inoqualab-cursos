<?php

namespace Tests\Feature\Managers\Users;

use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\User;
use App\Models\Users\Certificate;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresion: ActivitysController, CertificatesController, InscriptionsController
 * y ResultsController (todos bajo Managers\Users) leian $slack/$request->slack
 * de un usuario objetivo sin ningun guard de rol -- a diferencia de
 * UsersController::guardNotSuperadmin(), un manager con permisos completos
 * podia ver la actividad/certificados/inscripciones/resultados de una cuenta
 * superadmin, e incluso modificar el rango de fechas de su inscripcion.
 */
class SuperadminAccessGuardTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->manager = User::factory()->manager()->create();
        $this->superadmin = User::factory()->role('superadmin')->create();
    }

    private function certificateFor(User $owner): Certificate
    {
        return Certificate::create([
            'slack' => (string) Str::uuid(),
            'user_id' => $owner->id,
            'course_id' => Course::factory()->create()->id,
            'start_at' => now(),
            'end_at' => now()->addYear(),
        ]);
    }

    public function test_activitys_index_refuses_a_superadmin_target(): void
    {
        $this->actingAs($this->manager)
            ->get(route('manager.users.activitys', $this->superadmin->slack))
            ->assertForbidden();
    }

    public function test_activitys_lists_refuses_a_superadmin_target(): void
    {
        $this->actingAs($this->manager)
            ->post(route('manager.enterprises.users.lists'), ['slack' => $this->superadmin->slack])
            ->assertForbidden();
    }

    public function test_certificates_index_refuses_a_superadmin_target(): void
    {
        $this->actingAs($this->manager)
            ->get(route('manager.users.certificates', $this->superadmin->slack))
            ->assertForbidden();
    }

    public function test_results_index_refuses_a_superadmin_target(): void
    {
        $this->actingAs($this->manager)
            ->get(route('manager.users.results', $this->superadmin->slack))
            ->assertForbidden();
    }

    public function test_inscriptions_index_refuses_a_superadmin_target(): void
    {
        $this->actingAs($this->manager)
            ->get(route('manager.users.inscriptions', $this->superadmin->slack))
            ->assertForbidden();
    }

    public function test_inscriptions_action_refuses_to_touch_a_superadmin_inscription(): void
    {
        $inscription = Inscription::factory()->for($this->superadmin)->create();
        $originalEnrollStart = $inscription->enroll_start;

        $this->actingAs($this->manager)
            ->postJson(route('manager.users.inscriptions.action'), [
                'inscription' => $inscription->slack,
                'range' => '01/01/2026 - 01/06/2026',
            ])
            ->assertForbidden();

        $this->assertEquals($originalEnrollStart, $inscription->fresh()->enroll_start);
    }

    // ── Control positivo: el guard no bloquea cuentas normales ──────────

    public function test_activitys_index_allows_a_regular_customer_target(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($this->manager)
            ->get(route('manager.users.activitys', $customer->slack))
            ->assertOk();
    }

    public function test_certificates_index_allows_a_regular_customer_target(): void
    {
        $customer = User::factory()->customer()->create();
        $this->certificateFor($customer);

        $this->actingAs($this->manager)
            ->get(route('manager.users.certificates', $customer->slack))
            ->assertOk();
    }
}
