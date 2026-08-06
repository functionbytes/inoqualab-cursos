<?php

namespace Tests\Feature\Managers\Settings;

use App\Models\Setting\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: Setting::key() era un scope Eloquent que terminaba en
 * ->first(). Builder::callScope() hace `return $scope(...) ?? $this`, así
 * que sin fila para esa key, Eloquent devolvía el propio Builder en vez de
 * null. getLogo/getFavicon/getMetas llamaban ->getMedia() directo sobre ese
 * resultado -- BadMethodCallException ("Call to undefined method
 * Builder::getMedia()") en cualquier instalación nueva o la primera vez que
 * se pide el logo/favicon/metadata antes de subir uno. El propio código ya
 * tenía comentarios reconociendo el problema en index() (resuelto ahí con
 * firstOrCreate) pero no lo aplicaba en estos 3 métodos. Se eliminó el scope
 * y se migraron todos los call sites a firstOrCreate().
 */
class SettingsMediaTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->manager = User::factory()->manager()->create();
    }

    public function test_get_logo_does_not_500_when_the_setting_row_does_not_exist_yet(): void
    {
        $this->assertDatabaseMissing('settings', ['key' => 'page_logo']);

        $this->actingAs($this->manager)
            ->getJson(route('manager.settings.logo.get', 'page_logo'))
            ->assertOk()
            ->assertExactJson([]);

        // firstOrCreate() la deja creada para la próxima vez.
        $this->assertDatabaseHas('settings', ['key' => 'page_logo']);
    }

    public function test_get_favicon_does_not_500_when_the_setting_row_does_not_exist_yet(): void
    {
        $this->assertDatabaseMissing('settings', ['key' => 'page_favicon']);

        $this->actingAs($this->manager)
            ->getJson(route('manager.settings.favicon.get', 'page_favicon'))
            ->assertOk()
            ->assertExactJson([]);

        $this->assertDatabaseHas('settings', ['key' => 'page_favicon']);
    }

    public function test_get_metas_does_not_500_when_the_setting_row_does_not_exist_yet(): void
    {
        $this->assertDatabaseMissing('settings', ['key' => 'meta_image']);

        $this->actingAs($this->manager)
            ->getJson(route('manager.settings.metadata.get', 'meta_image'))
            ->assertOk()
            ->assertExactJson([]);

        $this->assertDatabaseHas('settings', ['key' => 'meta_image']);
    }

    public function test_get_logo_returns_media_when_the_setting_already_exists(): void
    {
        Setting::create(['key' => 'page_logo', 'value' => '']);

        $this->actingAs($this->manager)
            ->getJson(route('manager.settings.logo.get', 'page_logo'))
            ->assertOk()
            ->assertExactJson([]);
    }
}
