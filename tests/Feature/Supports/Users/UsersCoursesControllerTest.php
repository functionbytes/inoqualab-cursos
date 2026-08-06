<?php

namespace Tests\Feature\Supports\Users;

use App\Models\Inscription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresion de la auditoria del dominio Usuarios (portal soporte):
 * courses/postpone.blade.php era una vista huerfana copiada del portal
 * Enterprise self-service (`route('enterprise.dashboard')`,
 * `{{ inscription->slack }}` sin `$` -> constante indefinida -> 500 fatal).
 * Se reescribio para reutilizar el flujo de daterangepicker que ya
 * funcionaba en users/inscriptions/edit.blade.php.
 */
class UsersCoursesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->support = User::factory()->support()->create();
    }

    public function test_courses_index_lists_user_inscriptions(): void
    {
        $customer = User::factory()->customer()->create();
        $inscription = Inscription::factory()->for($customer)->create();

        $this->actingAs($this->support)
            ->get(route('support.users.courses.index', $customer->slack))
            ->assertOk()
            ->assertSee($inscription->course->title);
    }

    public function test_postpone_view_loads_without_fatal_error(): void
    {
        $customer = User::factory()->customer()->create();
        $inscription = Inscription::factory()->for($customer)->create();

        $this->actingAs($this->support)
            ->get(route('support.users.courses.postpone', $inscription->slack))
            ->assertOk()
            ->assertSee($inscription->course->title)
            ->assertSee(route('support.users.inscriptions.action'));
    }

    public function test_inscriptions_action_updates_enrollment_range(): void
    {
        $customer = User::factory()->customer()->create();
        $inscription = Inscription::factory()->for($customer)->create([
            'culminated' => 1,
            'expire' => 1,
        ]);

        $this->actingAs($this->support)
            ->postJson(route('support.users.inscriptions.action'), [
                'inscription' => $inscription->slack,
                'range' => '01/01/2026 - 01/06/2026',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $inscription->refresh();
        $this->assertEquals('2026-01-01', $inscription->enroll_start);
        $this->assertEquals('2026-06-01', $inscription->enroll_expire);
        $this->assertSame(0, (int) $inscription->culminated);
        $this->assertSame(0, (int) $inscription->expire);
    }

    public function test_destroy_removes_inscription(): void
    {
        $customer = User::factory()->customer()->create();
        $inscription = Inscription::factory()->for($customer)->create();

        $this->actingAs($this->support)
            ->delete(route('support.users.courses.destroy', $inscription->slack))
            ->assertRedirect();

        $this->assertNull(Inscription::find($inscription->id));
    }

    // ── Regresión: Inscription no usaba SoftDeletes pese a que la tabla ya ──
    // ── tenía deleted_at -- destroy() hacía HARD DELETE real, perdiendo el ──
    // ── historial de matrícula sin posibilidad de recuperarlo ────────────

    public function test_destroy_soft_deletes_the_inscription_instead_of_removing_it(): void
    {
        $customer = User::factory()->customer()->create();
        $inscription = Inscription::factory()->for($customer)->create();

        $this->actingAs($this->support)
            ->delete(route('support.users.courses.destroy', $inscription->slack))
            ->assertRedirect();

        $this->assertSoftDeleted('inscriptions', ['id' => $inscription->id]);
    }
}
