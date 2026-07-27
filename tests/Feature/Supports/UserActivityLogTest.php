<?php

namespace Tests\Feature\Supports;

use App\Models\Distributor\Distributor;
use App\Models\Enterprise\Enterprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * El historial de actividad de un usuario tenía tres fallos a la vez:
 *
 * 1. Ejecutaba `->get()` dos veces sobre activity_log (>1M filas) sin límite.
 * 2. Los filtros por modelo/propiedad se aplicaban al query DESPUÉS del get(),
 *    así que no filtraban nada.
 * 3. Los contadores por modelo salían siempre 0: se calculaban con
 *    `$activities->has('Enterprise')` sobre una colección plana, no agrupada.
 */
class UserActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $support;

    protected User $causer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->support = User::factory()->create(['role' => 'support']);
        $this->support->syncRoles(['support']);
        $this->causer = User::factory()->create(['role' => 'customer']);
    }

    private function logActivity(object $subject, string $description): void
    {
        activity()
            ->causedBy($this->causer)
            ->performedOn($subject)
            ->log($description);
    }

    public function test_counts_per_model_are_not_always_zero(): void
    {
        $this->logActivity(Enterprise::factory()->create(), 'creada');
        $this->logActivity(Enterprise::factory()->create(), 'actualizada');
        $this->logActivity(Distributor::factory()->create(), 'creado');

        $response = $this->actingAs($this->support)
            ->get(route('support.users.activitys', $this->causer->slack))
            ->assertOk();

        $counts = $response->viewData('counts');

        $this->assertSame(2, $counts['Enterprise']);
        $this->assertSame(1, $counts['Distributor']);
        $this->assertSame(0, $counts['Invoice']);
    }

    public function test_model_filter_actually_filters(): void
    {
        $this->logActivity(Enterprise::factory()->create(), 'de empresa');
        $this->logActivity(Distributor::factory()->create(), 'de distribuidor');

        $response = $this->actingAs($this->support)
            ->get(route('support.users.activitys', $this->causer->slack).'?model=Enterprise\\Enterprise')
            ->assertOk();

        $activities = $response->viewData('activities');

        $this->assertCount(1, $activities);
        $this->assertSame('de empresa', $activities->first()->description);
    }

    public function test_listing_is_paginated_not_a_full_table_load(): void
    {
        foreach (range(1, 30) as $i) {
            $this->logActivity(Enterprise::factory()->create(), "actividad {$i}");
        }

        $response = $this->actingAs($this->support)
            ->get(route('support.users.activitys', $this->causer->slack))
            ->assertOk();

        $activities = $response->viewData('activities');

        $this->assertInstanceOf(LengthAwarePaginator::class, $activities);
        $this->assertSame(30, $activities->total());
        $this->assertLessThanOrEqual(paginationNumber(), $activities->count());
    }

    public function test_ajax_list_is_capped(): void
    {
        foreach (range(1, 12) as $i) {
            $this->logActivity(Enterprise::factory()->create(), "actividad {$i}");
        }

        $this->actingAs($this->support)
            ->postJson(route('support.users.activitys.lists'), [
                'slack' => $this->causer->slack,
                'limit' => 5,
            ])
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_only_the_requested_users_activity_is_listed(): void
    {
        $otherCauser = User::factory()->create();
        activity()->causedBy($otherCauser)
            ->performedOn(Enterprise::factory()->create())
            ->log('de otro usuario');

        $this->logActivity(Enterprise::factory()->create(), 'del usuario consultado');

        $response = $this->actingAs($this->support)
            ->get(route('support.users.activitys', $this->causer->slack))
            ->assertOk();

        // Ojo: Enterprise usa LogsActivity, así que las propias factories
        // generan entradas sin causer. Solo se comparan las de cada causer.
        $this->assertSame(1, $response->viewData('activities')->total());
        $this->assertSame(1, Activity::causedBy($this->causer)->count());
        $this->assertSame(1, Activity::causedBy($otherCauser)->count());
    }
}
