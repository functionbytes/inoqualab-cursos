<?php

namespace Tests\Feature\Customers;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El layout del portal de cliente heredaba la lista de librerías del panel de
 * gestión y cargaba ~570 KB por página que el alumno no usa: select2, quill,
 * dropzone, moment, daterangepicker y jquery-validation, sin una sola
 * referencia en las 35 vistas del portal.
 *
 * Este test evita que vuelvan a colarse al copiar y pegar del layout de
 * managers, que es de donde salieron.
 */
class PortalAssetsTest extends TestCase
{
    use RefreshDatabase;

    /** Librerías que el portal del alumno no debe volver a cargar. */
    private const NOT_NEEDED = [
        'select2',
        'quill',
        'dropzone',
        'moment.js',
        'daterangepicker',
        'jquery.validate',
        // Familias de iconos sin un solo uso; tabler además está prohibido
        // por las reglas del proyecto (aquí se usa Font Awesome 6).
        'tabler-icons',
        'cryptocoins',
        'flag-icon',
    ];

    /** Solo las líneas que cargan un asset, para no chocar con los comentarios. */
    private function assetLines(): string
    {
        $layout = file_get_contents(resource_path('views/layouts/customers.blade.php'));

        preg_match_all("/url\\('[^']*\\.(?:css|js)'\\)/", $layout, $matches);

        return implode("\n", $matches[0]);
    }

    public function test_portal_layout_does_not_load_unused_libraries(): void
    {
        $layout = $this->assetLines();

        foreach (self::NOT_NEEDED as $lib) {
            $this->assertStringNotContainsString(
                $lib,
                $layout,
                "El layout del portal volvió a cargar {$lib}, que ninguna de sus vistas usa."
            );
        }
    }

    public function test_stylesheet_does_not_import_unused_icon_sets(): void
    {
        // Los sets de iconos entraban por @import dentro de style.css, no por
        // el layout, así que hay que mirar también ahí.
        $css = file_get_contents(public_path('customers/css/style.css'));
        $head = substr($css, 0, 2000);

        foreach (['tabler-icons', 'cryptocoins', 'flag-icon'] as $iconSet) {
            $this->assertStringNotContainsString(
                $iconSet,
                $head,
                "style.css volvió a importar {$iconSet}."
            );
        }

        // Este @import apuntaba a un archivo inexistente y provocaba un 404
        // en cada carga del portal.
        $this->assertStringNotContainsString('../libs/simplebar', $head);
    }

    public function test_libraries_actually_in_use_are_still_loaded(): void
    {
        // Contrapeso: que la limpieza no se lleve por delante lo que sí hace falta.
        $layout = $this->assetLines();

        foreach (['jquery', 'bootstrap', 'toastr', 'simplebar', 'owl.carousel', 'fontawesome'] as $lib) {
            $this->assertStringContainsString($lib, $layout, "Falta {$lib}, que el portal sí usa.");
        }
    }

    public function test_portal_pages_still_render(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $customer = User::factory()->create(['role' => 'customer']);
        $customer->assignRole('customer');

        foreach (['customers.dashboard', 'customers.courses', 'customers.settings'] as $route) {
            $this->actingAs($customer)->get(route($route))->assertOk();
        }
    }
}
