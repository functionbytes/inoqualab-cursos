<?php

namespace Tests\Feature\Managers\Mailer;

use App\Models\Mailer\MailerVariable;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * create() y edit() devolvían la vista sin `categories` ni `modules`, que las
 * plantillas recorren para pintar los dos selects: ambas pantallas morían con
 * "Undefined variable $categories". El catálogo vive ahora en constantes del
 * modelo, así que las opciones no dependen de que ya existan filas en la tabla.
 */
class MailerVariableFormsTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    public function test_create_form_renders_with_category_and_module_options(): void
    {
        $this->actingAs($this->manager)
            ->get(route('mailers.variables.create'))
            ->assertOk()
            ->assertSee('Usuario', false)
            ->assertSee('Órdenes', false);
    }

    public function test_edit_form_renders(): void
    {
        $variable = MailerVariable::create([
            'key' => 'NOMBRE_CLIENTE',
            'name' => 'Nombre del cliente',
            'category' => 'user',
            'module' => 'core',
            'is_system' => false,
            'is_enabled' => true,
        ]);

        $this->actingAs($this->manager)
            ->get(route('mailers.variables.edit', $variable))
            ->assertOk()
            ->assertSee('NOMBRE_CLIENTE', false);
    }

    public function test_catalogs_cover_the_values_in_use(): void
    {
        // Valores que ya existen en la tabla de producción: si alguien recorta
        // el catálogo, el select dejaría de poder representarlos y el guardado
        // los perdería en silencio.
        foreach (['user', 'site', 'company', 'date', 'links', 'order', 'newsletter'] as $category) {
            $this->assertArrayHasKey($category, MailerVariable::CATEGORIES);
        }
        foreach (['core', 'orders', 'newsletter'] as $module) {
            $this->assertArrayHasKey($module, MailerVariable::MODULES);
        }
    }

    public function test_variable_can_be_stored_with_a_catalog_category(): void
    {
        $this->actingAs($this->manager)
            ->post(route('mailers.variables.store'), [
                'key' => 'FECHA_VENCIMIENTO',
                'name' => 'Fecha de vencimiento',
                'category' => 'date',
                'module' => 'orders',
                'is_enabled' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('mailer_variables', [
            'key' => 'FECHA_VENCIMIENTO',
            'category' => 'date',
            'module' => 'orders',
        ]);
    }
}
