<?php

namespace Tests\Feature\Distributors\Enterprises;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorCourse;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseCourse;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Dos regresiones distintas del mismo controller:
 *
 * 1. destroy() redirigía a route('manager.enterprises.courses', ...) --
 *    dominio Managers -- en vez de la ruta equivalente de este portal.
 * 2. update() adjuntaba a la empresa CUALQUIER curso del catálogo global
 *    recibido en el request, sin verificar que perteneciera al catálogo
 *    contratado del distribuidor (distributor_courses). Eso permitía
 *    otorgar acceso gratuito (vía includes()/enrollSimple()) a cursos por
 *    los que el distribuidor nunca pagó tarifa (DistributorCourse::tariff())
 *    -- un bypass de facturación.
 */
class CourseControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $distributorStaff;

    private Distributor $distributor;

    private Enterprise $enterprise;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->distributor = Distributor::factory()->create();
        $this->distributorStaff = User::factory()->create(['role' => 'distributor']);
        DB::table('distributor_staff')->insert([
            'distributor_id' => $this->distributor->id,
            'user_id' => $this->distributorStaff->id,
        ]);

        $this->enterprise = Enterprise::factory()->create();
        DistributorEnterprise::create([
            'distributor_id' => $this->distributor->id,
            'enterprise_id' => $this->enterprise->id,
            'available' => 1,
        ]);
    }

    public function test_destroy_redirects_to_the_distributor_portal_not_managers(): void
    {
        $course = Course::factory()->create();
        EnterpriseCourse::create([
            'course_id' => $course->id,
            'enterprise_id' => $this->enterprise->id,
        ]);

        // Antes: route('manager.enterprises.courses', ...) -- ruta del panel
        // Managers, no del portal distributor.
        $this->actingAs($this->distributorStaff)
            ->delete(route('distributor.enterprises.courses.destroy', [$this->enterprise->slack, $course->slack]))
            ->assertRedirect(route('distributor.enterprises.courses', $this->enterprise->slack));
    }

    public function test_update_only_attaches_courses_in_the_distributor_catalog(): void
    {
        $contracted = Course::factory()->create();
        DistributorCourse::create([
            'course_id' => $contracted->id,
            'distributor_id' => $this->distributor->id,
            'price' => 50000,
        ]);

        // Curso del catálogo global que el distribuidor NUNCA contrató (sin
        // fila en distributor_courses, sin tarifa).
        $notContracted = Course::factory()->create();

        $this->actingAs($this->distributorStaff)
            ->postJson(route('distributor.enterprises.courses.assign.update'), [
                'slack' => $this->enterprise->slack,
                'courses' => "{$contracted->id},{$notContracted->id}",
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('enterprise_course', [
            'enterprise_id' => $this->enterprise->id,
            'course_id' => $contracted->id,
        ]);
        // El curso no contratado NO debe quedar adjuntado -- sin este fix,
        // la empresa lo recibía gratis igual.
        $this->assertDatabaseMissing('enterprise_course', [
            'enterprise_id' => $this->enterprise->id,
            'course_id' => $notContracted->id,
        ]);
    }

    public function test_update_reports_failure_when_no_requested_course_is_in_the_catalog(): void
    {
        $notContracted = Course::factory()->create();

        $this->actingAs($this->distributorStaff)
            ->postJson(route('distributor.enterprises.courses.assign.update'), [
                'slack' => $this->enterprise->slack,
                'courses' => (string) $notContracted->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', false);

        $this->assertDatabaseMissing('enterprise_course', [
            'enterprise_id' => $this->enterprise->id,
            'course_id' => $notContracted->id,
        ]);
    }

    // ── Regresión: includes() (matrícula masiva vía lista de identificaciones) ──
    // ── llamaba a User::identification(), que aborta 404 si no hay match -- ──
    // ── una identificación con typo abortaba TODO el lote a mitad de camino ──

    public function test_includes_enrolls_valid_users_even_with_an_invalid_identification_in_the_list(): void
    {
        // Requeridos por InscriptionService::enrollSimple() -> createSimpleEnrollment().
        OrderCondition::firstOrCreate(['slug' => 'payment'], ['slack' => 'oc-payment', 'title' => 'Pagada']);
        OrderType::firstOrCreate(['slug' => 'services'], ['slack' => 'ot-services', 'title' => 'Servicios']);
        OrderMethod::firstOrCreate(['slug' => 'credit'], ['slack' => 'om-credit', 'title' => 'Crédito']);

        $course = Course::factory()->create();
        EnterpriseCourse::create(['course_id' => $course->id, 'enterprise_id' => $this->enterprise->id]);

        $user = User::factory()->create(['identification' => 'VALID'.uniqid()]);
        DB::table('enterprise_user')->insert([
            'user_id' => $user->id,
            'enterprise_id' => $this->enterprise->id,
            'available' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->distributorStaff)
            ->postJson(route('distributor.enterprises.action.reasign'), [
                'enterprise' => $this->enterprise->slack,
                'course' => $course->slack,
                // 'NO-EXISTE' no pertenece a ningún usuario -- antes del fix,
                // esto abortaba con 404 crudo antes de matricular a $user.
                'users' => 'NO-EXISTE,'.$user->identification,
            ])
            ->assertOk();

        $this->assertDatabaseHas('inscriptions', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
    }
}
