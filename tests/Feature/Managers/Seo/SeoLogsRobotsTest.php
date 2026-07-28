<?php

namespace Tests\Feature\Managers\Seo;

use App\Models\Seo\Seo404Log;
use App\Models\Seo\SeoRedirect;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Cobertura de los logs 404 (creación de redirect atómica) y del editor de
 * robots.txt (escritura a disco resiliente). Mockea File para no tocar el
 * robots.txt real del proyecto durante los tests.
 */
class SeoLogsRobotsTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    public function test_create_redirect_from_404_is_atomic(): void
    {
        $log = Seo404Log::create([
            'path' => '/pagina-rota',
            'hit_count' => 5,
            'has_redirect' => false,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.logs.create-redirect'), [
                'log_id' => $log->id,
                'target_path' => '/pagina-nueva',
                'status_code' => 301,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertTrue($log->fresh()->has_redirect);
        $this->assertSame(1, SeoRedirect::where('target_path', '/pagina-nueva')->count());
    }

    public function test_robots_update_persists_the_setting(): void
    {
        // El robots.txt ya no se escribe en disco: la fuente de verdad es el
        // ajuste y lo sirve RobotsTxtController. Ver RobotsTxtSourceOfTruthTest.
        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.robots.update'), ['robots_txt' => "User-agent: *\nDisallow: /panel/"])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('settings', ['key' => 'robots_txt']);
    }

    public function test_robots_update_no_longer_depends_on_disk(): void
    {
        // Antes un fallo de escritura devolvía 500. Al quitar el archivo
        // estático, guardar no puede fallar por disco: solo se comprueba si
        // quedó un robots.txt de la versión anterior para borrarlo.
        File::shouldReceive('put')->never();
        File::shouldReceive('exists')->andReturnFalse();
        File::shouldReceive('delete')->never();

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.robots.update'), ['robots_txt' => 'User-agent: *'])
            ->assertOk()
            ->assertJson(['success' => true]);
    }
}
