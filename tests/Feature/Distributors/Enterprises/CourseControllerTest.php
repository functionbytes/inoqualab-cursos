<?php

namespace Tests\Feature\Distributors\Enterprises;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseCourse;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresión: Distributors\Enterprises\CourseController::destroy() redirigía
 * a route('manager.enterprises.courses', ...) -- dominio Managers -- en vez
 * de la ruta equivalente de este portal. Mismo bug que
 * EnterprisesControllerTest::destroy, otro método distinto del mismo dominio.
 */
class CourseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_destroy_redirects_to_the_distributor_portal_not_managers(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $distributor = Distributor::factory()->create();
        $staff = User::factory()->create(['role' => 'distributor']);
        DB::table('distributor_staff')->insert([
            'distributor_id' => $distributor->id,
            'user_id' => $staff->id,
        ]);

        $enterprise = Enterprise::factory()->create();
        DistributorEnterprise::create([
            'distributor_id' => $distributor->id,
            'enterprise_id' => $enterprise->id,
            'available' => 1,
        ]);

        $course = Course::factory()->create();
        EnterpriseCourse::create([
            'course_id' => $course->id,
            'enterprise_id' => $enterprise->id,
        ]);

        // Antes: route('manager.enterprises.courses', ...) -- ruta del panel
        // Managers, no del portal distributor.
        $this->actingAs($staff)
            ->delete(route('distributor.enterprises.courses.destroy', [$enterprise->slack, $course->slack]))
            ->assertRedirect(route('distributor.enterprises.courses', $enterprise->slack));
    }
}
