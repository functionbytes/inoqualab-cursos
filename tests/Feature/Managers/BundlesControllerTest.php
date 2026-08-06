<?php

namespace Tests\Feature\Managers;

use App\Models\Bundle\Bundle;
use App\Models\Course\Course;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: update()/store() adjuntaban cursos con
 * attach($key, ['course_id' => $id]) en un loop, sin transacción y sin
 * validar que los ids existieran. Un id inexistente a mitad de la lista
 * tiraba QueryException por la FK de bundle_course -- en update(), como
 * detach() ya se había ejecutado y confirmado antes del loop, el bundle
 * quedaba SIN NINGÚN curso, incluidos los que estaban correctos.
 */
class BundlesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->manager = User::factory()->manager()->create();
    }

    private function makeBundle(): Bundle
    {
        return Bundle::create([
            'slack' => 'bundle-'.uniqid(),
            'title' => 'Paquete original',
            'slug' => 'paquete-'.uniqid(),
            'price' => 100000,
            'available' => 1,
        ]);
    }

    public function test_update_ignores_nonexistent_course_ids_without_losing_the_valid_ones(): void
    {
        $bundle = $this->makeBundle();
        $courseA = Course::factory()->create();
        $courseB = Course::factory()->create();
        $bundle->courses()->attach([$courseA->id]);

        $nonexistentId = Course::max('id') + 999;

        $this->actingAs($this->manager)
            ->postJson(route('manager.bundles.update'), [
                'slack' => $bundle->slack,
                'title' => $bundle->title,
                'price' => 100000,
                'start_date' => Carbon::today()->toDateString(),
                'expire_at' => Carbon::today()->addYear()->toDateString(),
                // curso válido nuevo + un id que ya no existe.
                'courses' => "{$courseB->id},{$nonexistentId}",
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        // El curso válido quedó asociado -- antes, con el bug, el bundle
        // quedaba sin NINGÚN curso (ni siquiera courseB) si el id inexistente
        // rompía el loop de attach().
        $this->assertDatabaseHas('bundle_course', ['bundle_id' => $bundle->id, 'course_id' => $courseB->id]);
        $this->assertDatabaseMissing('bundle_course', ['bundle_id' => $bundle->id, 'course_id' => $nonexistentId]);
        // El curso anterior (courseA) sí se reemplaza, como pide el formulario.
        $this->assertDatabaseMissing('bundle_course', ['bundle_id' => $bundle->id, 'course_id' => $courseA->id]);
    }

    public function test_update_replaces_the_course_list_with_valid_ids(): void
    {
        $bundle = $this->makeBundle();
        $courseA = Course::factory()->create();
        $courseB = Course::factory()->create();
        $bundle->courses()->attach([$courseA->id]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.bundles.update'), [
                'slack' => $bundle->slack,
                'title' => $bundle->title,
                'price' => 100000,
                'start_date' => Carbon::today()->toDateString(),
                'expire_at' => Carbon::today()->addYear()->toDateString(),
                'courses' => (string) $courseB->id,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame([$courseB->id], $bundle->courses()->pluck('courses.id')->all());
    }

    // ── Regresión: Bundle no usaba SoftDeletes pese a que la tabla ya tenía ──
    // ── deleted_at (con índice, desde su creación) -- destroy() hacía HARD ──
    // ── DELETE real en vez de soft-delete ─────────────────────────────────

    public function test_destroy_soft_deletes_the_bundle_instead_of_removing_it(): void
    {
        $bundle = $this->makeBundle();

        $this->actingAs($this->manager)
            ->delete(route('manager.bundles.destroy', $bundle->slack))
            ->assertRedirect(route('manager.bundles'));

        $this->assertSoftDeleted('bundles', ['id' => $bundle->id]);
    }
}
