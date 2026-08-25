<?php

namespace Tests\Feature\Supports\Enterprises;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorCourse;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseCourse;
use App\Models\User;
use Database\Seeders\CatalogsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresión IDOR de negocio (hallazgo de auditoría, no salto de tenant):
 * enroll()/store() validaban que el usuario perteneciera a la empresa, pero
 * NUNCA que el curso estuviera en el catálogo asignado a esa empresa
 * (enterprise_course). Con tarifa distribuidor↔curso configurada, un agente
 * de soporte podía matricular a un empleado en un curso que su empresa
 * nunca contrató, con solo cambiar el `course` del payload.
 */
class InscriptionsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    private Distributor $distributor;

    private Enterprise $enterprise;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(CatalogsSeeder::class);

        $this->support = User::factory()->create(['role' => 'support']);

        $this->distributor = Distributor::factory()->create();
        $this->enterprise = Enterprise::factory()->create();
        DistributorEnterprise::create([
            'distributor_id' => $this->distributor->id,
            'enterprise_id' => $this->enterprise->id,
        ]);

        $this->employee = User::factory()->create();
        DB::table('enterprise_user')->insert([
            'user_id' => $this->employee->id,
            'enterprise_id' => $this->enterprise->id,
            'available' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_enroll_rejects_a_course_not_assigned_to_the_enterprise(): void
    {
        // Tiene tarifa con el distribuidor, pero NUNCA se asignó a esta empresa.
        $course = Course::factory()->create();
        DistributorCourse::create([
            'course_id' => $course->id,
            'distributor_id' => $this->distributor->id,
            'price' => 50000,
        ]);

        $this->actingAs($this->support)
            ->postJson(route('support.enterprises.inscriptions.enroll'), [
                'enterprise' => $this->enterprise->id,
                'course' => $course->id,
                'user' => $this->employee->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('inscriptions', [
            'user_id' => $this->employee->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_enroll_succeeds_for_a_course_in_the_enterprise_catalog(): void
    {
        $course = Course::factory()->create();
        DistributorCourse::create([
            'course_id' => $course->id,
            'distributor_id' => $this->distributor->id,
            'price' => 50000,
        ]);
        EnterpriseCourse::create([
            'course_id' => $course->id,
            'enterprise_id' => $this->enterprise->id,
        ]);

        $this->actingAs($this->support)
            ->postJson(route('support.enterprises.inscriptions.enroll'), [
                'enterprise' => $this->enterprise->id,
                'course' => $course->id,
                'user' => $this->employee->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('inscriptions', [
            'user_id' => $this->employee->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_store_rejects_a_course_not_assigned_to_the_enterprise(): void
    {
        $course = Course::factory()->create();
        DistributorCourse::create([
            'course_id' => $course->id,
            'distributor_id' => $this->distributor->id,
            'price' => 50000,
        ]);

        $this->actingAs($this->support)
            ->postJson(route('support.enterprises.inscriptions.store'), [
                'enterprise' => $this->enterprise->slack,
                'course' => $course->id,
                'user' => $this->employee->identification,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('inscriptions', [
            'user_id' => $this->employee->id,
            'course_id' => $course->id,
        ]);
    }
}
