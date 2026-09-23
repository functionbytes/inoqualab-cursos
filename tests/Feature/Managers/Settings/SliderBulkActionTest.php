<?php

namespace Tests\Feature\Managers\Settings;

use App\Models\Slider;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SliderBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    protected function makeSlider(array $overrides = []): Slider
    {
        return Slider::create(array_merge([
            'slack' => Str::random(10),
            'title' => 'Banner '.Str::random(6),
            'subtitle' => 'Subtitulo',
            'description' => 'Descripcion',
            'available' => 1,
            'position' => 1,
            'ubication' => 'center',
            'url' => '/destino',
        ], $overrides));
    }

    public function test_bulk_publish_marks_sliders_available(): void
    {
        $slider = $this->makeSlider(['available' => 0]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.sliders.bulk-action'), ['action' => 'publish', 'ids' => [$slider->id]])
            ->assertOk();

        $this->assertSame(1, $slider->fresh()->available);
    }

    public function test_bulk_hide_marks_sliders_unavailable(): void
    {
        $slider = $this->makeSlider(['available' => 1]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.sliders.bulk-action'), ['action' => 'hide', 'ids' => [$slider->id]])
            ->assertOk();

        $this->assertSame(0, $slider->fresh()->available);
    }

    public function test_bulk_delete_removes_sliders(): void
    {
        $a = $this->makeSlider();
        $b = $this->makeSlider();

        $this->actingAs($this->manager)
            ->postJson(route('manager.sliders.bulk-action'), ['action' => 'delete', 'ids' => [$a->id, $b->id]])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => '2 slider(s) procesados.']);

        $this->assertDatabaseCount('sliders', 0);
    }

    public function test_bulk_action_rejects_invalid_action(): void
    {
        $slider = $this->makeSlider();

        $this->actingAs($this->manager)
            ->postJson(route('manager.sliders.bulk-action'), ['action' => 'archive', 'ids' => [$slider->id]])
            ->assertUnprocessable();

        $this->assertDatabaseCount('sliders', 1);
    }

    public function test_bulk_delete_forbidden_without_delete_permission(): void
    {
        $slider = $this->makeSlider();
        Role::findByName('manager')->revokePermissionTo('sliders.delete');

        $this->actingAs(User::factory()->manager()->create())
            ->postJson(route('manager.sliders.bulk-action'), ['action' => 'delete', 'ids' => [$slider->id]])
            ->assertForbidden();

        $this->assertDatabaseCount('sliders', 1);
    }
}
