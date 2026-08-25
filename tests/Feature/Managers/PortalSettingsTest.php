<?php

namespace Tests\Feature\Managers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PortalSettingsTest extends TestCase
{
    use RefreshDatabase;

    /** Todas las claves son obligatorias: el formulario las envía siempre. */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'customers_nav_layout' => 'horizontal',
            'customers_dashboard_variant' => 'a',
            'customers_courses_variant' => 'a',
            'customers_certificates_variant' => 'a',
            'customers_orders_variant' => 'a',
            'customers_documents_variant' => 'a',
            'customers_settings_variant' => 'a',
            'customers_notifications_variant' => 'a',
            'aula_version' => '1',
        ], $overrides);
    }

    private function manager(): User
    {
        Permission::findOrCreate('settings.update', 'web');

        $manager = User::factory()->manager()->create();
        $manager->givePermissionTo('settings.update');

        return $manager->fresh();
    }

    public function test_manager_can_open_portal_settings(): void
    {
        $this->actingAs($this->manager())
            ->get('/panel/settings/portal')
            ->assertOk()
            ->assertSee('Portal del alumno')
            ->assertSee('Ruta formativa')
            ->assertSee('Expediente en lista')
            ->assertSee('Aula / Lección')
            ->assertSee('Barra lateral')
            ->assertSee('Lista y vista previa')
            ->assertSee('Compras agrupadas')
            ->assertSee('Biblioteca')
            ->assertSee('Carnet y pestañas')
            ->assertSee('Bandeja');
    }

    public function test_manager_can_switch_variants(): void
    {
        $this->actingAs($this->manager())
            ->post('/panel/settings/portal/update', $this->payload([
                'customers_dashboard_variant' => 'b',
                'customers_nav_layout' => 'vertical',
                'customers_notifications_variant' => 'b',
                'aula_version' => '2',
            ]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('settings', [
            'key' => 'customers_dashboard_variant',
            'value' => 'b',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'customers_nav_layout',
            'value' => 'vertical',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'customers_notifications_variant',
            'value' => 'b',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'aula_version',
            'value' => '2',
        ]);
    }

    /**
     * El valor se concatena al nombre de la vista, así que un valor arbitrario
     * no puede llegar nunca a la tabla settings.
     */
    public function test_invalid_variant_is_rejected(): void
    {
        $this->actingAs($this->manager())
            ->postJson('/panel/settings/portal/update', $this->payload([
                'customers_dashboard_variant' => '-b/../../etc/passwd',
            ]))
            ->assertUnprocessable();

        $this->assertDatabaseMissing('settings', [
            'key' => 'customers_dashboard_variant',
            'value' => '-b/../../etc/passwd',
        ]);
    }

    public function test_customer_cannot_open_portal_settings(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get('/panel/settings/portal')
            ->assertRedirect();
    }
}
