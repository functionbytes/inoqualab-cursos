<?php

namespace Tests\Feature\Supports\Enterprises;

use App\Models\Course\Course;
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
 * Regresión (mismo bug que Distributors\Enterprises\CourseController,
 * replicado en el equivalente de Supports): includes() llamaba a
 * User::identification($user) dentro de un foreach. Ese scope aborta con
 * 404 si no hay match -- eso hacía que el "! $validate instanceof User" ya
 * escrito en el método fuera código muerto inalcanzable: una identificación
 * con typo abortaba la matrícula masiva COMPLETA con un 404 crudo.
 */
class CourseControllerIncludesTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->support = User::factory()->create(['role' => 'support']);
        $this->support->syncRoles(['support']);

        // Requeridos por InscriptionService::enrollSimple() -> createSimpleEnrollment().
        OrderCondition::firstOrCreate(['slug' => 'payment'], ['slack' => 'oc-payment', 'title' => 'Pagada']);
        OrderType::firstOrCreate(['slug' => 'services'], ['slack' => 'ot-services', 'title' => 'Servicios']);
        OrderMethod::firstOrCreate(['slug' => 'credit'], ['slack' => 'om-credit', 'title' => 'Crédito']);
    }

    public function test_includes_enrolls_valid_users_even_with_an_invalid_identification_in_the_list(): void
    {
        $enterprise = Enterprise::factory()->create();
        $course = Course::factory()->create();
        EnterpriseCourse::create(['course_id' => $course->id, 'enterprise_id' => $enterprise->id]);

        $user = User::factory()->create(['identification' => 'VALID'.uniqid()]);
        DB::table('enterprise_user')->insert([
            'user_id' => $user->id,
            'enterprise_id' => $enterprise->id,
            'available' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->support)
            ->postJson(route('support.enterprises.action.reasign'), [
                'enterprise' => $enterprise->slack,
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
