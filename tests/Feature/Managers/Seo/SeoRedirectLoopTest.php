<?php

namespace Tests\Feature\Managers\Seo;

use App\Models\Seo\SeoRedirect;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: SeoRedirectController::store()/update() no impedía crear un redirect
 * que apunta a sí mismo (A→A) ni uno que, combinado con otro existente, cierra un
 * ciclo (A→B, B→A). HandleSeoRedirects resuelve un único hop por request sin
 * límite de profundidad, así que un ciclo activo produce un bucle infinito de
 * redirecciones para cualquier visitante público.
 */
class SeoRedirectLoopTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    public function test_store_rejects_redirect_pointing_to_itself(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.redirects.store'), [
                'source_path' => '/loop',
                'target_path' => '/loop',
                'status_code' => 301,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('target_path');

        $this->assertDatabaseCount('seo_redirects', 0);
    }

    public function test_store_rejects_redirect_that_forms_a_cycle_with_an_existing_one(): void
    {
        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.redirects.store'), [
                'source_path' => '/a',
                'target_path' => '/b',
                'status_code' => 301,
            ])
            ->assertOk();

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.redirects.store'), [
                'source_path' => '/b',
                'target_path' => '/a',
                'status_code' => 301,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('target_path');

        $this->assertDatabaseCount('seo_redirects', 1);
        $this->assertDatabaseHas('seo_redirects', ['source_path' => '/a', 'target_path' => '/b']);
    }

    public function test_store_allows_a_legitimate_non_cyclic_chain(): void
    {
        // A -> B -> C (sin ciclo) sigue permitido: es un caso normal de contenido
        // movido dos veces; la detección/aplanado de cadenas es una acción manual
        // (RedirectChainDetector::resolveAll), no debe bloquear el guardado.
        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.redirects.store'), [
                'source_path' => '/a',
                'target_path' => '/b',
                'status_code' => 301,
            ])
            ->assertOk();

        $this->actingAs($this->manager)
            ->postJson(route('manager.seo.redirects.store'), [
                'source_path' => '/b',
                'target_path' => '/c',
                'status_code' => 301,
            ])
            ->assertOk();

        $this->assertDatabaseCount('seo_redirects', 2);
    }

    public function test_update_rejects_change_that_would_create_a_cycle(): void
    {
        SeoRedirect::create(['source_path' => '/a', 'target_path' => '/b', 'status_code' => 301, 'is_active' => true]);
        $second = SeoRedirect::create(['source_path' => '/c', 'target_path' => '/d', 'status_code' => 301, 'is_active' => true]);

        $this->actingAs($this->manager)
            ->putJson(route('manager.seo.redirects.update', $second), [
                'source_path' => '/c',
                'target_path' => '/a',
                'status_code' => 301,
            ])
            ->assertOk();

        // No forma ciclo (c -> a -> b, termina), así que sigue permitido.
        $this->assertSame('/a', $second->fresh()->target_path);

        // Pero actualizar /a para apuntar de vuelta a /c sí cerraría el ciclo.
        $first = SeoRedirect::where('source_path', '/a')->first();

        $this->actingAs($this->manager)
            ->putJson(route('manager.seo.redirects.update', $first), [
                'source_path' => '/a',
                'target_path' => '/c',
                'status_code' => 301,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('target_path');

        $this->assertSame('/b', $first->fresh()->target_path);
    }

    public function test_update_allows_saving_the_record_unchanged(): void
    {
        // Regresión: sin excluir el propio registro (ignoreId) del grafo, revalidar
        // los mismos valores podía detectar un falso ciclo contra sí mismo.
        $redirect = SeoRedirect::create(['source_path' => '/a', 'target_path' => '/b', 'status_code' => 301, 'is_active' => true]);

        $this->actingAs($this->manager)
            ->putJson(route('manager.seo.redirects.update', $redirect), [
                'source_path' => '/a',
                'target_path' => '/b',
                'status_code' => 302,
            ])
            ->assertOk();

        $this->assertSame(302, $redirect->fresh()->status_code);
    }
}
