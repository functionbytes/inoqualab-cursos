<?php

namespace Tests\Feature\Managers\Bundles;

use App\Models\Bundle\Bundle;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BundleToggleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeBundle(int $available = 1): Bundle
    {
        return Bundle::create([
            'slack' => Str::random(6),
            'title' => 'Paquete '.Str::random(4),
            'price' => 75000,
            'available' => $available,
        ]);
    }

    public function test_manager_with_permission_can_toggle_availability(): void
    {
        $bundle = $this->makeBundle(1);
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->postJson(route('manager.bundles.toggle'), ['slack' => $bundle->slack])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('available', 0);

        $this->assertSame(0, (int) $bundle->fresh()->available);
    }

    public function test_toggle_forbidden_without_update_permission(): void
    {
        $bundle = $this->makeBundle(1);
        Role::findByName('manager')->revokePermissionTo('bundles.update');
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->postJson(route('manager.bundles.toggle'), ['slack' => $bundle->slack])
            ->assertForbidden();

        // El estado no cambió.
        $this->assertSame(1, (int) $bundle->fresh()->available);
    }
}
