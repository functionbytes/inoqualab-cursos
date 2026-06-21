<?php

namespace Tests\Feature\Authorization;

use App\Http\Middleware\EnforcePanelPermission;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Fija el comportamiento del middleware de autorización por convención:
 * deriva {dominio}.{verbo} del nombre de ruta, respeta los alias de naming
 * (singular/typo/nombre corto) y mantiene el fail-open documentado para
 * dominios sin permiso definido.
 */
class EnforcePanelPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        // Rutas efímeras que ejercitan la convención de nombres del panel.
        Route::middleware(EnforcePanelPermission::class)->group(function () {
            Route::get('/_t/seo', fn () => 'ok')->name('manager.seo.index');
            Route::get('/_t/newsletter', fn () => 'ok')->name('manager.newsletter.index');
            Route::get('/_t/order/edit', fn () => 'ok')->name('manager.order.edit');
            Route::get('/_t/mails', fn () => 'ok')->name('manager.mails.index');
            Route::get('/_t/departaments/view', fn () => 'ok')->name('manager.departaments.view');
            Route::get('/_t/unknown', fn () => 'ok')->name('manager.unknown-domain.index');
        });
    }

    public function test_new_domain_requires_its_permission(): void
    {
        // seo.view existe en el catálogo: customer (sin él) 403, manager 200.
        $this->actingAs(User::factory()->customer()->create())
            ->get('/_t/seo')->assertForbidden();

        $this->actingAs(User::factory()->manager()->create())
            ->get('/_t/seo')->assertOk();
    }

    public function test_newsletter_alias_maps_to_plural_permission(): void
    {
        // Ruta `manager.newsletter.*` (singular) -> permiso `newsletters.view`.
        $this->actingAs(User::factory()->customer()->create())
            ->get('/_t/newsletter')->assertForbidden();

        $this->actingAs(User::factory()->manager()->create())
            ->get('/_t/newsletter')->assertOk();
    }

    public function test_order_alias_maps_to_plural_update_permission(): void
    {
        // Ruta `manager.order.edit` (singular) -> permiso `orders.update`.
        $this->actingAs(User::factory()->customer()->create())
            ->get('/_t/order/edit')->assertForbidden();

        $this->actingAs(User::factory()->manager()->create())
            ->get('/_t/order/edit')->assertOk();
    }

    public function test_mails_alias_maps_to_incoming_mails_permission(): void
    {
        // Ruta `manager.mails.*` (nombre corto) -> permiso `incoming-mails.view`.
        $this->actingAs(User::factory()->customer()->create())
            ->get('/_t/mails')->assertForbidden();

        $this->actingAs(User::factory()->manager()->create())
            ->get('/_t/mails')->assertOk();
    }

    public function test_departaments_typo_alias_maps_to_departments_permission(): void
    {
        // Errata histórica `departaments` -> permiso real `departments.view`.
        // support tiene departments.view; customer no.
        $this->actingAs(User::factory()->customer()->create())
            ->get('/_t/departaments/view')->assertForbidden();

        $this->actingAs(User::factory()->support()->create())
            ->get('/_t/departaments/view')->assertOk();
    }

    public function test_domain_without_permission_is_fail_open(): void
    {
        // `unknown-domain.view` no existe en el catálogo: el middleware deja
        // pasar (comportamiento documentado), aun para un customer.
        $this->actingAs(User::factory()->customer()->create())
            ->get('/_t/unknown')->assertOk();
    }
}
