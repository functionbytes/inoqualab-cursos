<?php

namespace Tests\Feature\Inscriptions;

use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'available' => 1,
            'validation' => 1,
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'available' => 1,
            'validation' => 1,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    private function makeInscription(?User $user = null, ?Course $course = null): Inscription
    {
        $user ??= $this->customer;
        $course ??= Course::factory()->create();

        return Inscription::create([
            'slack' => 'insc-'.uniqid(),
            'user_id' => $user->id,
            'course_id' => $course->id,
            'percent' => 0,
            'enroll_start' => Carbon::now(),
            'enroll_expire' => Carbon::now()->addMonths(3),
            'culminated' => 0,
            'expire' => 0,
        ]);
    }

    // ── Authorization ─────────────────────────────────────────────────────────────

    public function test_guest_is_redirected_when_accessing_inscriptions_index(): void
    {
        $this->get(route('manager.users.inscriptions', $this->customer->slack))
            ->assertRedirect();
    }

    public function test_non_manager_cannot_access_inscriptions_index(): void
    {
        $this->actingAs($this->customer)
            ->get(route('manager.users.inscriptions', $this->customer->slack))
            ->assertRedirect();
    }

    // ── Happy path ────────────────────────────────────────────────────────────────

    public function test_manager_can_view_user_inscriptions_list(): void
    {
        $this->makeInscription($this->customer);

        $this->actingAs($this->manager)
            ->get(route('manager.users.inscriptions', $this->customer->slack))
            ->assertOk();
    }

    public function test_manager_can_view_inscription_edit_page(): void
    {
        $inscription = $this->makeInscription($this->customer);

        $this->actingAs($this->manager)
            ->get(route('manager.users.inscriptions.edit', $inscription->slack))
            ->assertOk();
    }

    public function test_manager_can_update_inscription_dates(): void
    {
        $inscription = $this->makeInscription($this->customer);

        $start = Carbon::now()->format('d/m/Y');
        $end = Carbon::now()->addMonths(6)->format('d/m/Y');

        $this->actingAs($this->manager)
            ->postJson(route('manager.users.inscriptions.action'), [
                'inscription' => $inscription->slack,
                'range' => "{$start} - {$end}",
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('inscriptions', [
            'id' => $inscription->id,
            'culminated' => 0,
            'expire' => 0,
        ]);
    }

    // ── Validation / edge cases ───────────────────────────────────────────────────

    public function test_inscription_action_fails_for_nonexistent_slack(): void
    {
        // scopeSlack aborts 404 when the model is not found.
        $this->actingAs($this->manager)
            ->postJson(route('manager.users.inscriptions.action'), [
                'inscription' => 'does-not-exist',
                'range' => '01/01/2025 - 01/06/2025',
            ])
            ->assertNotFound();
    }

    public function test_inscription_action_updates_both_dates_correctly(): void
    {
        $inscription = $this->makeInscription($this->customer);

        $newStart = '01/01/2025';
        $newEnd = '30/06/2025';

        $this->actingAs($this->manager)
            ->postJson(route('manager.users.inscriptions.action'), [
                'inscription' => $inscription->slack,
                'range' => "{$newStart} - {$newEnd}",
            ])
            ->assertOk();

        $updated = $inscription->fresh();
        $this->assertEquals('2025-01-01', $updated->enroll_start);
        $this->assertEquals('2025-06-30', $updated->enroll_expire);
    }

    public function test_manager_can_view_inscriptions_for_user_with_no_enrollments(): void
    {
        // User exists but has zero inscriptions.
        $emptyUser = User::factory()->create(['role' => 'customer', 'available' => 1]);

        $this->actingAs($this->manager)
            ->get(route('manager.users.inscriptions', $emptyUser->slack))
            ->assertOk();
    }
}
