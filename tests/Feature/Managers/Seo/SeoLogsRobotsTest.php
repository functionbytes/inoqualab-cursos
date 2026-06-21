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

    public function test_robots_update_succeeds_when_file_writes(): void
    {
        File::shouldReceive('put')->once()->andReturnTrue();

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.robots.update'), ['robots_txt' => "User-agent: *\nDisallow: /panel/"])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_robots_update_reports_file_write_failure(): void
    {
        // El fix envuelve File::put en try/catch: un fallo de disco devuelve 500
        // con mensaje claro en vez de un error crudo, sin desincronizar.
        File::shouldReceive('put')->once()->andThrow(new \RuntimeException('disk full'));

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.robots.update'), ['robots_txt' => 'User-agent: *'])
            ->assertStatus(500)
            ->assertJson(['success' => false]);
    }
}
