<?php

namespace Tests\Feature\Supports;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: Supports\Distributors\CourseController::update(),
 * Supports\Distributors\EnterpriseController::updateAssignments(),
 * y Supports\Enterprises\CourseController::update() hacían
 * detach()+attach() en loop suelto, sin DB::transaction() -- el mismo
 * patrón ya corregido en BundlesController::update() (un attach() fallido
 * a mitad de camino dejaba el registro con MENOS asociaciones que antes y
 * ninguna nueva). Se reemplazó por sync() atómico, filtrando primero contra
 * ids reales (estos controllers no tenían Form Request con exists:).
 */
class DistributorEnterpriseCourseAssignmentSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->support = User::factory()->create(['role' => 'support', 'available' => 1, 'validation' => 1]);
    }

    public function test_distributor_courses_update_replaces_the_previous_assignment(): void
    {
        $distributor = Distributor::factory()->create();
        $old = Course::factory()->create();
        $new = Course::factory()->create();
        $distributor->courses()->attach($old->id);

        $this->actingAs($this->support)
            ->postJson(route('support.distributors.courses.update'), [
                'slack' => $distributor->slack,
                'courses' => (string) $new->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('distributor_courses', ['distributor_id' => $distributor->id, 'course_id' => $old->id]);
        $this->assertDatabaseHas('distributor_courses', ['distributor_id' => $distributor->id, 'course_id' => $new->id]);
    }

    public function test_distributor_courses_update_ignores_nonexistent_ids_without_500(): void
    {
        $distributor = Distributor::factory()->create();
        $course = Course::factory()->create();
        $nonexistent = Course::max('id') + 999;

        $this->actingAs($this->support)
            ->postJson(route('support.distributors.courses.update'), [
                'slack' => $distributor->slack,
                'courses' => "{$course->id},{$nonexistent}",
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('distributor_courses', ['distributor_id' => $distributor->id, 'course_id' => $course->id]);
        $this->assertDatabaseMissing('distributor_courses', ['distributor_id' => $distributor->id, 'course_id' => $nonexistent]);
    }

    public function test_distributor_enterprises_assignments_update_replaces_the_previous_assignment(): void
    {
        $distributor = Distributor::factory()->create();
        $old = Enterprise::factory()->create();
        $new = Enterprise::factory()->create();
        $distributor->enterprises()->attach($old->id);

        $this->actingAs($this->support)
            ->postJson(route('support.distributors.enterprises.assignments.update'), [
                'slack' => $distributor->slack,
                'enterprises' => (string) $new->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('distributor_enterprises', ['distributor_id' => $distributor->id, 'enterprise_id' => $old->id]);
        $this->assertDatabaseHas('distributor_enterprises', ['distributor_id' => $distributor->id, 'enterprise_id' => $new->id]);
    }

    public function test_enterprise_courses_update_replaces_the_previous_assignment(): void
    {
        $enterprise = Enterprise::factory()->create();
        $old = Course::factory()->create();
        $new = Course::factory()->create();
        $enterprise->courses()->attach($old->id);

        $this->actingAs($this->support)
            ->postJson(route('support.enterprises.courses.assign.update'), [
                'slack' => $enterprise->slack,
                'courses' => (string) $new->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('enterprise_course', ['enterprise_id' => $enterprise->id, 'course_id' => $old->id]);
        $this->assertDatabaseHas('enterprise_course', ['enterprise_id' => $enterprise->id, 'course_id' => $new->id]);
    }
}
