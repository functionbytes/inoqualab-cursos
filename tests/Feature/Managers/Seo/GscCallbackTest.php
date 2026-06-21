<?php

namespace Tests\Feature\Managers\Seo;

use App\Models\User;
use App\Services\GoogleSearchConsoleService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El callback de OAuth de Google Search Console debe validar el parámetro
 * `state` (anti-CSRF). Con un state inválido no debe canjear el código.
 */
class GscCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    public function test_callback_rejects_invalid_state(): void
    {
        // El service no debe ser invocado si el state no valida.
        $this->mock(GoogleSearchConsoleService::class)
            ->shouldReceive('handleCallback')->never();

        $this->actingAs($this->manager)
            ->get(route('manager.seo.gsc.callback', ['code' => 'fake-code', 'state' => 'estado-falsificado']))
            ->assertRedirect(route('manager.seo.gsc.index'))
            ->assertSessionHas('error');
    }

    public function test_callback_rejects_missing_state(): void
    {
        $this->mock(GoogleSearchConsoleService::class)
            ->shouldReceive('handleCallback')->never();

        $this->actingAs($this->manager)
            ->get(route('manager.seo.gsc.callback', ['code' => 'fake-code']))
            ->assertRedirect(route('manager.seo.gsc.index'))
            ->assertSessionHas('error');
    }
}
