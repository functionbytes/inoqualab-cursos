<?php

namespace Tests\Feature\Managers;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Smoke test de los módulos del panel añadidos recientemente (SEO, Newsletter,
 * Mailer). Verifica que sus páginas index cargan para un manager con permisos
 * (no 403 por el enforcement nuevo, no 500 por errores de vista) tras haber
 * añadido los permisos `seo.*` y los alias de `newsletters`.
 */
class NewModulesSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function panelIndexRoutes(): array
    {
        return [
            'seo dashboard' => ['manager.seo.dashboard'],
            'seo metas' => ['manager.seo.metas.index'],
            'seo redirects' => ['manager.seo.redirects.index'],
            'seo robots' => ['manager.seo.robots.index'],
            'seo sitemap' => ['manager.seo.sitemap.index'],
            'seo logs' => ['manager.seo.logs.index'],
            'seo templates' => ['manager.seo.templates.index'],
            'seo page-urls' => ['manager.seo.page-urls.index'],
            'seo web-vitals' => ['manager.seo.web-vitals.index'],
            'seo llms' => ['manager.seo.llms.index'],
            'seo orphans' => ['manager.seo.orphans.index'],
            'seo report' => ['manager.seo.report.index'],
            'seo alerts' => ['manager.seo.alerts.index'],
            'newsletter' => ['manager.newsletter.index'],
            'mailer templates' => ['mailers.templates.index'],
        ];
    }

    #[DataProvider('panelIndexRoutes')]
    public function test_panel_index_loads_for_manager(string $routeName): void
    {
        $response = $this->actingAs($this->manager)->get(route($routeName));

        $this->assertNotSame(403, $response->getStatusCode(), "{$routeName} devolvió 403 (regresión de permisos)");
        $this->assertNotSame(500, $response->getStatusCode(), "{$routeName} devolvió 500 (error de carga)");
        $response->assertOk();
    }

    public function test_mailer_requires_newsletter_permission(): void
    {
        // El grupo `mailers.*` exige `newsletters.view` (cierra el hueco del
        // naming `mailers.` que el middleware por convención no cubre).
        // Un usuario con rol de panel manager (columna `role`, pasa IsManager)
        // pero SIN el permiso heredado debe recibir 403.
        $user = User::factory()->manager()->create();
        $user->syncRoles([]);                 // quita permisos heredados del rol Spatie
        $user->givePermissionTo('orders.view'); // algún permiso, pero no newsletters.view
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($user->fresh())
            ->get(route('mailers.templates.index'))
            ->assertForbidden();
    }
}
