<?php

namespace Tests\Feature\Managers\Distributors;

use App\Models\Course\Course;
use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorCourse;
use App\Models\Enterprise\Enterprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DistributorAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected Distributor $distributor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
        $this->distributor = Distributor::factory()->create();
    }

    private function createEnterprise(): Enterprise
    {
        $enterprise = new Enterprise([
            'slack' => (string) Str::uuid(),
            'title' => 'EMPRESA PRUEBA',
            'nit' => '900'.rand(100000, 999999).'-'.rand(0, 9),
            'available' => 1,
        ]);
        $enterprise->slug = Str::slug($enterprise->title, '-');
        $enterprise->email = fake()->unique()->safeEmail();
        $enterprise->save();

        return $enterprise;
    }

    public function test_assigning_nonexistent_course_id_fails_validation_instead_of_500(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.courses.update'), [
                'slack' => $this->distributor->slack,
                'courses' => '999999',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['courses.0']);
    }

    public function test_assigning_valid_course_succeeds(): void
    {
        $course = Course::factory()->create();

        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.courses.update'), [
                'slack' => $this->distributor->slack,
                'courses' => (string) $course->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('distributor_courses', [
            'distributor_id' => $this->distributor->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_assigning_nonexistent_enterprise_id_fails_validation_instead_of_500(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.enterprises.update'), [
                'slack' => $this->distributor->slack,
                'enterprises' => '999999',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['enterprises.0']);
    }

    public function test_assigning_valid_enterprise_succeeds(): void
    {
        $enterprise = $this->createEnterprise();

        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.enterprises.update'), [
                'slack' => $this->distributor->slack,
                'enterprises' => (string) $enterprise->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('distributor_enterprises', [
            'distributor_id' => $this->distributor->id,
            'enterprise_id' => $enterprise->id,
        ]);
    }

    private function createRate(int $distributorId, int $courseId, float $price): DistributorCourse
    {
        // 'price' no está en $fillable de DistributorCourse; se asigna por propiedad directa.
        $rate = new DistributorCourse([
            'distributor_id' => $distributorId,
            'course_id' => $courseId,
        ]);
        $rate->price = $price;
        $rate->save();

        return $rate;
    }

    public function test_rates_update_rejects_non_numeric_price(): void
    {
        $course = Course::factory()->create();
        $rate = $this->createRate($this->distributor->id, $course->id, 1000);

        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.rates.update'), [
                'slack' => $this->distributor->slack,
                'courses' => [$rate->id => 'not-a-number'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(["courses.{$rate->id}"]);
    }

    public function test_rates_update_does_not_overwrite_price_of_foreign_distributor_course(): void
    {
        $otherDistributor = Distributor::factory()->create();
        $course = Course::factory()->create();
        $foreignRate = $this->createRate($otherDistributor->id, $course->id, 5000);

        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.rates.update'), [
                'slack' => $this->distributor->slack,
                'courses' => [$foreignRate->id => 99999],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('distributor_courses', [
            'id' => $foreignRate->id,
            'price' => 5000,
        ]);
    }

    // ── Regresión: detach()+attach() en loop suelto (sin transacción) ──────
    // ── se reemplazó por sync() atómico -- se verifica el comportamiento ───
    // ── de "reemplazo" (el curso/empresa anterior se quita, no se acumula) ──

    public function test_updating_courses_replaces_the_previous_assignment_not_accumulates(): void
    {
        $old = Course::factory()->create();
        $new = Course::factory()->create();
        $this->distributor->courses()->attach($old->id);

        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.courses.update'), [
                'slack' => $this->distributor->slack,
                'courses' => (string) $new->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('distributor_courses', ['distributor_id' => $this->distributor->id, 'course_id' => $old->id]);
        $this->assertDatabaseHas('distributor_courses', ['distributor_id' => $this->distributor->id, 'course_id' => $new->id]);
    }

    public function test_updating_enterprises_replaces_the_previous_assignment_not_accumulates(): void
    {
        $old = $this->createEnterprise();
        $new = $this->createEnterprise();
        $this->distributor->enterprises()->attach($old->id);

        $this->actingAs($this->manager)
            ->postJson(route('manager.distributors.enterprises.update'), [
                'slack' => $this->distributor->slack,
                'enterprises' => (string) $new->id,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('distributor_enterprises', ['distributor_id' => $this->distributor->id, 'enterprise_id' => $old->id]);
        $this->assertDatabaseHas('distributor_enterprises', ['distributor_id' => $this->distributor->id, 'enterprise_id' => $new->id]);
    }

    public function test_unauthorized_user_cannot_update_courses(): void
    {
        $restrictedManager = User::factory()->manager()->create();
        $restrictedManager->syncRoles([]);

        $this->actingAs($restrictedManager)
            ->postJson(route('manager.distributors.courses.update'), [
                'slack' => $this->distributor->slack,
                'courses' => '',
            ])
            ->assertForbidden();
    }
}
