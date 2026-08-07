<?php

namespace Tests\Feature\Managers\Enterprises;

use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseCourse;
use App\Models\Inscription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: actionReasign() llamaba a User::identification($identifier)
 * dentro de un foreach envuelto en DB::transaction(). Ese scope aborta con
 * 404 si no hay match -- una sola identificación con typo abortaba TODA la
 * reasignación masiva con un 404 crudo (revirtiendo, gracias a la
 * transacción, lo ya reasignado en esa misma corrida) en vez de omitir la
 * entrada inválida como ya hace con un usuario ajeno a la empresa.
 */
class CourseControllerReassignTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private Enterprise $enterprise;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
        $this->enterprise = Enterprise::factory()->create();
    }

    public function test_reassign_processes_valid_users_even_with_an_invalid_identification_in_the_list(): void
    {
        $oldCourse = Course::factory()->create();
        $newCourse = Course::factory()->create();
        EnterpriseCourse::create(['course_id' => $oldCourse->id, 'enterprise_id' => $this->enterprise->id]);
        EnterpriseCourse::create(['course_id' => $newCourse->id, 'enterprise_id' => $this->enterprise->id]);

        $user = User::factory()->create(['identification' => 'VALID'.uniqid()]);
        \DB::table('enterprise_user')->insert([
            'user_id' => $user->id,
            'enterprise_id' => $this->enterprise->id,
            'available' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $inscription = Inscription::factory()->create([
            'user_id' => $user->id,
            'course_id' => $oldCourse->id,
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.enterprises.action.reasign'), [
                'enterprise' => $this->enterprise->id,
                'old' => $oldCourse->id,
                'course' => $newCourse->id,
                // 'NO-EXISTE' no pertenece a ningún usuario -- antes del fix,
                // esto abortaba con 404 crudo antes de reasignar a $user.
                'users' => ['NO-EXISTE', $user->identification],
            ])
            ->assertRedirect();

        $this->assertSame($newCourse->id, $inscription->fresh()->course_id);
    }
}
