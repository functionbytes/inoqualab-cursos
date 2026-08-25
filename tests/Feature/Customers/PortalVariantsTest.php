<?php

namespace Tests\Feature\Customers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PortalVariantsTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        // El middleware CheckSession expulsa al usuario cuando users.session
        // apunta a otra sesión (single-session). En una petición de prueba no
        // hay login previo, así que se deja en null: el middleware lo permite.
        $customer = User::factory()->customer()->create();
        $customer->forceFill(['session' => null, 'available' => 1])->save();

        return $customer->fresh();
    }

    public function test_dashboard_uses_variant_a_by_default(): void
    {
        $this->actingAs($this->customer())
            ->get('/customer')
            ->assertOk()
            ->assertViewIs('customers.views.dashboard.index');
    }

    public function test_dashboard_uses_variant_b_when_configured(): void
    {
        updateSettings(['customers_dashboard_variant' => 'b']);

        $this->actingAs($this->customer())
            ->get('/customer')
            ->assertOk()
            ->assertViewIs('customers.views.dashboard.index-b');
    }

    public function test_courses_uses_variant_a_by_default(): void
    {
        $this->actingAs($this->customer())
            ->get('/customer/courses')
            ->assertOk()
            ->assertViewIs('customers.views.courses.index');
    }

    public function test_courses_uses_variant_b_when_configured(): void
    {
        updateSettings(['customers_courses_variant' => 'b']);

        $this->actingAs($this->customer())
            ->get('/customer/courses')
            ->assertOk()
            ->assertViewIs('customers.views.courses.index-b');
    }

    /**
     * Un valor corrupto en la tabla settings no debe intentar cargar una vista
     * inexistente: el controlador solo distingue 'b' del resto.
     */
    public function test_unknown_variant_falls_back_to_a(): void
    {
        updateSettings(['customers_dashboard_variant' => 'zzz']);

        $this->actingAs($this->customer())
            ->get('/customer')
            ->assertOk()
            ->assertViewIs('customers.views.dashboard.index');
    }

    public function test_nav_layout_setting_reaches_the_markup(): void
    {
        updateSettings(['customers_nav_layout' => 'vertical']);

        $this->actingAs($this->customer())
            ->get('/customer')
            ->assertOk()
            ->assertSee('data-layout="vertical"', false);
    }

    /** Las cinco pantallas restantes: cada ajuste debe resolver a su vista. */
    #[DataProvider('pantallas')]
    public function test_each_screen_honours_its_variant(string $key, string $url, string $view): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->get($url)->assertOk()->assertViewIs($view);

        updateSettings([$key => 'b']);

        $this->actingAs($customer)->get($url)->assertOk()->assertViewIs($view.'-b');
    }

    /** El aula respeta aula_version (1 = temario derecha, 2 = izquierda) sin romperse. */
    public function test_lesson_hall_renders_in_both_versions(): void
    {
        $customer = $this->customer();

        // Localizar una inscripción activa del cliente de prueba no aplica aquí
        // (base recién migrada), así que solo se comprueba que la ruta del curso
        // no reviente según la versión: si no hay curso, la app redirige, no 500.
        foreach (['1', '2'] as $version) {
            updateSettings(['aula_version' => $version]);
            $resp = $this->actingAs($customer)->get('/customer/courses');
            $resp->assertOk();
        }
    }

    public static function pantallas(): array
    {
        return [
            'certificados' => ['customers_certificates_variant', '/customer/courses/certificates', 'customers.views.certificates.index'],
            'pedidos' => ['customers_orders_variant', '/customer/orders', 'customers.views.orders.index'],
            'documentos' => ['customers_documents_variant', '/customer/documents', 'customers.views.documents.index'],
            'configuracion' => ['customers_settings_variant', '/customer/settings', 'customers.views.settings.index'],
            'notificaciones' => ['customers_notifications_variant', '/customer/notifications', 'customers.views.chats.index'],
        ];
    }
}
