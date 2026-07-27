<?php

namespace Tests\Feature\Managers\Seo;

use App\Models\Seo\SeoMeta;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La vista schema-org/edit escribía `@context` y `@type` como texto literal
 * dentro de un placeholder JSON-LD y de un <code>. `@context` ES una directiva
 * real de Blade (Illuminate\View\Compilers\Concerns\CompilesContexts), así que
 * el compilador abría un `if` que ningún `@endcontext` cerraba: la plantilla
 * compilaba a PHP inválido y la pantalla reventaba con un error fatal.
 *
 * Escapado a `@@context` / `@@type`, que Blade emite como literal.
 */
class SchemaOrgEditRendersTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_org_edit_page_renders(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $manager = User::factory()->manager()->create();

        $meta = SeoMeta::create([
            'seoable_type' => 'App\Models\Course\Course',
            'seoable_id' => 1,
            'locale' => 'es',
            'title' => 'Meta de prueba',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('manager.seo.schema-org.edit', $meta))
            ->assertOk();

        // El literal debe llegar al HTML sin que Blade lo trate como directiva.
        $response->assertSee('"@context"', false);
        $response->assertSee('<code>@context</code>', false);
    }
}
