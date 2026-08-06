<?php

namespace Tests\Feature\Managers\Enterprises;

use App\Models\Course\Course;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseCourse;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: RatesController::update() no tenía Form Request ni ninguna
 * regla de validación sobre `courses` (a diferencia de su gemelo
 * Distributors/RatesController, que sí exige numeric|min:0 vía
 * UpdateDistributorRatesRequest) -- se podía guardar un precio negativo o no
 * numérico en enterprise_course.
 */
class RatesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private Enterprise $enterprise;

    private EnterpriseCourse $rate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->manager = User::factory()->manager()->create();
        $this->enterprise = Enterprise::factory()->create();
        $course = Course::factory()->create();

        // 'price' no está en $fillable de EnterpriseCourse (solo se escribe
        // desde RatesController::update()); hay que asignarlo aparte.
        $this->rate = EnterpriseCourse::create([
            'course_id' => $course->id,
            'enterprise_id' => $this->enterprise->id,
        ]);
        $this->rate->price = 50000;
        $this->rate->save();
    }

    public function test_update_rejects_a_negative_price(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.enterprises.rates.update'), [
                'slack' => $this->enterprise->slack,
                'courses' => [$this->rate->id => -100],
            ])
            ->assertStatus(422);

        $this->assertSame('50000.00', $this->rate->fresh()->price);
    }

    public function test_update_rejects_a_non_numeric_price(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.enterprises.rates.update'), [
                'slack' => $this->enterprise->slack,
                'courses' => [$this->rate->id => 'no-es-un-numero'],
            ])
            ->assertStatus(422);

        $this->assertSame('50000.00', $this->rate->fresh()->price);
    }

    public function test_update_accepts_a_valid_price(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.enterprises.rates.update'), [
                'slack' => $this->enterprise->slack,
                'courses' => [$this->rate->id => 75000],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('75000.00', $this->rate->fresh()->price);
    }
}
