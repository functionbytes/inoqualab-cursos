<?php

namespace Tests\Feature\Managers\Settings;

use App\Models\Setting\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: metadata/setting.blade.php y settings/setting.blade.php
 * imprimían valores de Setting con {!! !!} dentro de un atributo value="..."
 * (meta_description, meta_image, page_logo, page_favicon) o dentro de un
 * <textarea> sin pasar por el purificador (page_description/page_politic/
 * page_term) -- mismo patrón ya arreglado antes en
 * seo/schema-org/edit.blade.php: una comilla doble en el valor rompe el
 * atributo y permite inyectar HTML/JS arbitrario que se ejecuta en el
 * navegador de cualquier admin que abra esa página de settings.
 */
class SettingsXssTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    public function test_meta_description_attribute_breakout_is_escaped(): void
    {
        Setting::create(['key' => 'meta_description', 'value' => '"><script>alert(1)</script>']);

        $response = $this->actingAs($this->manager)->get(route('manager.settings.metadata'));

        $response->assertOk();
        $response->assertDontSee('"><script>alert(1)</script>', false);
        $response->assertSee('&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_page_logo_attribute_breakout_is_escaped(): void
    {
        Setting::create(['key' => 'page_logo', 'value' => '"><script>alert(1)</script>']);

        $response = $this->actingAs($this->manager)->get(route('manager.settings'));

        $response->assertOk();
        $response->assertDontSee('"><script>alert(1)</script>', false);
    }

    public function test_page_description_content_is_purified(): void
    {
        Setting::create(['key' => 'page_description', 'value' => '<p>Hola</p><script>alert(1)</script>']);

        $response = $this->actingAs($this->manager)->get(route('manager.settings'));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        // El contenido legítimo (permitido por el perfil 'content') sobrevive.
        $response->assertSee('<p>Hola</p>', false);
    }
}
