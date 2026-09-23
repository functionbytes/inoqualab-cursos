<?php

namespace Tests\Feature\Managers\Courses;

use App\Models\Course\Course;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresión: CoursesController::edit() armaba $certifications/$certifiers sin
 * una opción vacía (a diferencia de create(), que sí la tenía). Un curso sin
 * certification_id/certifier_id asignado (ambos nullable en BD) se editaba
 * con el <select> cayendo en la primera opción real por falta de una vacía
 * que coincidiera con null -- guardar sin tocar esos selects le asignaba en
 * silencio el primer certificador/certificación de la lista.
 */
class CoursesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    public function test_edit_view_offers_a_blank_option_for_certification_and_certifier(): void
    {
        $course = Course::factory()->create();

        $response = $this->actingAs($this->manager)
            ->get(route('manager.courses.edit', $course->slack))
            ->assertOk();

        $response->assertViewHas('certifications', function ($certifications) {
            return $certifications->has('');
        });

        $response->assertViewHas('certifiers', function ($certifiers) {
            return $certifiers->has('');
        });
    }

    public function test_update_keeps_certification_and_certifier_null_when_course_never_had_them(): void
    {
        $course = Course::factory()->create();
        $this->assertNull($course->certification_id);
        $this->assertNull($course->certifier_id);

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.update'), [
                'slack' => $course->slack,
                'title' => $course->title,
                'categorie' => $course->categorie_id,
                'available' => 1,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $course->refresh();
        $this->assertNull($course->certification_id);
        $this->assertNull($course->certifier_id);
    }

    public function test_update_does_not_require_day_duration_certification_or_certifier(): void
    {
        $course = Course::factory()->create();

        $this->actingAs($this->manager)
            ->postJson(route('manager.courses.update'), [
                'slack' => $course->slack,
                'title' => 'Curso actualizado sin datos opcionales',
                'categorie' => $course->categorie_id,
                'available' => 1,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('courses', [
            'slack' => $course->slack,
            'title' => 'CURSO ACTUALIZADO SIN DATOS OPCIONALES',
        ]);
    }

    public function test_store_does_not_require_day_duration_certification_or_certifier(): void
    {
        $categoryId = \DB::table('course_categories')->insertGetId([
            'slack' => Str::random(10),
            'title' => 'Categoria de prueba',
            'slug' => 'categoria-de-prueba-'.Str::random(4),
        ]);

        $response = $this->actingAs($this->manager)
            ->postJson(route('manager.courses.store'), [
                'title' => 'Curso nuevo sin datos opcionales',
                'categorie' => $categoryId,
            ])
            ->assertOk();

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('courses', [
            'slack' => $response->json('slack'),
            'title' => 'CURSO NUEVO SIN DATOS OPCIONALES',
            'day' => null,
            'duration' => null,
            'certification_id' => null,
            'certifier_id' => null,
        ]);
    }
}
