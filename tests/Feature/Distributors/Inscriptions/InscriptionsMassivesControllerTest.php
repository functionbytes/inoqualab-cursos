<?php

namespace Tests\Feature\Distributors\Inscriptions;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorCourse;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\Order\OrderCondition;
use App\Models\Order\OrderMethod;
use App\Models\Order\OrderType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresión: store() (matrícula masiva) llamaba a User::identification() y
 * Course::id() dentro de un foreach. Ambos scopes abortan con 404 si no hay
 * match -- una sola identificación/id con typo en un lote grande abortaba el
 * request COMPLETO con un 404 crudo, dejando las matrículas/órdenes YA
 * creadas por iteraciones previas (cada una con su propia DB::transaction())
 * y el resto del lote sin procesar, en vez de sumarse a $errors[] como el
 * resto de fallos que esta misma función ya reporta con gracia.
 */
class InscriptionsMassivesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $distributorStaff;

    private Distributor $distributor;

    private Enterprise $enterprise;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        OrderCondition::firstOrCreate(['slug' => 'payment'], ['slack' => 'oc-payment', 'title' => 'Pagada']);
        OrderType::firstOrCreate(['slug' => 'services'], ['slack' => 'ot-services', 'title' => 'Servicios']);
        OrderMethod::firstOrCreate(['slug' => 'credit'], ['slack' => 'om-credit', 'title' => 'Crédito']);

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

    private function enrollUser(): User
    {
        $user = User::factory()->create(['identification' => 'VALID'.uniqid()]);
        EnterpriseUser::create([
            'user_id' => $user->id,
            'enterprise_id' => $this->enterprise->id,
            'available' => 1,
        ]);

        return $user;
    }

    private function tariffedCourse(): Course
    {
        $course = Course::factory()->create();
        $rate = new DistributorCourse([
            'distributor_id' => $this->distributor->id,
            'course_id' => $course->id,
        ]);
        $rate->price = 100000;
        $rate->save();

        return $course;
    }

    public function test_a_nonexistent_identification_is_reported_as_an_error_not_a_500_or_404(): void
    {
        $course = $this->tariffedCourse();

        $response = $this->actingAs($this->distributorStaff)
            ->postJson(route('distributor.inscriptions.massives.store'), [
                'enterprise' => $this->enterprise->slack,
                'courses' => (string) $course->id,
                // 'NO-EXISTE' no pertenece a ningún usuario -- antes del fix,
                // esto abortaba con 404 crudo en vez de sumarse a errors[].
                'users' => 'NO-EXISTE',
            ]);

        $response->assertOk();
        $this->assertNotEmpty($response->json('errors'));
        $this->assertStringContainsString(
            'No existe ningún usuario',
            collect($response->json('errors'))->first()['message']
        );
    }

    public function test_valid_users_after_an_invalid_one_are_still_enrolled(): void
    {
        $course = $this->tariffedCourse();
        $validUser = $this->enrollUser();

        // Orden invertida internamente (array_reverse en el controller): el
        // usuario válido queda primero en el foreach real, así que además se
        // prueba explícitamente que uno inválido en la lista no bloquea al
        // resto ordenando el válido primero en el input.
        $response = $this->actingAs($this->distributorStaff)
            ->postJson(route('distributor.inscriptions.massives.store'), [
                'enterprise' => $this->enterprise->slack,
                'courses' => (string) $course->id,
                'users' => 'NO-EXISTE,'.$validUser->identification,
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('inscriptions', [
            'user_id' => $validUser->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_a_nonexistent_course_id_is_reported_as_an_error_not_a_500_or_404(): void
    {
        $validUser = $this->enrollUser();
        $nonexistentCourseId = Course::max('id') + 999;

        $response = $this->actingAs($this->distributorStaff)
            ->postJson(route('distributor.inscriptions.massives.store'), [
                'enterprise' => $this->enterprise->slack,
                'courses' => (string) $nonexistentCourseId,
                'users' => $validUser->identification,
            ]);

        $response->assertOk();
        $this->assertNotEmpty($response->json('errors'));
        $this->assertStringContainsString(
            'No existe ningún curso',
            collect($response->json('errors'))->first()['message']
        );
    }
}
