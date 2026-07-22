<?php

namespace Tests\Feature\Managers\Settings;

use App\Models\Bundle\Bundle;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Regresión de dos familias de bugs encontrados en el dominio Settings:
 *
 * 1. Nombre de ruta equivocado (copy-paste entre sub-módulos): la vista de
 *    "removedfile" de Dropzone llamaba a la ruta de OTRO módulo
 *    (metadata -> certifiers, trusteds/create -> sliders).
 * 2. Método HTTP equivocado: las vistas llamaban con `type: 'GET'` a rutas
 *    registradas exclusivamente como DELETE (405 Method Not Allowed),
 *    dejando roto silenciosamente el flujo "quitar archivo antes de guardar".
 *
 * Estos tests fijan el contrato: las rutas de borrado de thumbnails/archivos
 * solo aceptan DELETE, y BundlesController (que no tenía ninguna protección
 * ni null-safety) queda igualado al resto de controllers hermanos.
 */
class SettingsFileDeleteRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function managerUser(): User
    {
        return User::factory()->manager()->create();
    }

    private function userWithoutPermissions(): User
    {
        $user = User::factory()->manager()->create();
        $user->syncRoles([]);
        $user->syncPermissions([]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    public function test_metadata_delete_route_only_accepts_delete_method(): void
    {
        $manager = $this->managerUser();

        // El método usado antes del fix (GET) debe seguir sin existir como ruta valida.
        $this->actingAs($manager)
            ->get(route('manager.settings.metadata.delete', ['id' => 999999]))
            ->assertMethodNotAllowed();

        // El método correcto (DELETE, ya usado por la vista tras el fix) debe funcionar.
        $this->actingAs($manager)
            ->deleteJson(route('manager.settings.metadata.delete', ['id' => 999999]))
            ->assertOk()
            ->assertJson(['status' => 'success']);
    }

    public function test_trusteds_thumbnails_delete_route_only_accepts_delete_method(): void
    {
        $manager = $this->managerUser();

        $this->actingAs($manager)
            ->get(route('manager.trusteds.thumbnails.delete', ['id' => 999999]))
            ->assertMethodNotAllowed();

        $this->actingAs($manager)
            ->deleteJson(route('manager.trusteds.thumbnails.delete', ['id' => 999999]))
            ->assertOk()
            ->assertJson(['status' => 'success']);
    }

    public function test_metadata_edit_view_points_to_its_own_delete_route_not_certifiers(): void
    {
        $manager = $this->managerUser();

        $response = $this->actingAs($manager)->get(route('manager.settings.metadata'));

        $response->assertOk();
        $response->assertSee(route('manager.settings.metadata.delete', [':id']), false);
        $response->assertDontSee('certifiers/delete/thumbnails');
    }

    public function test_trusteds_create_view_points_to_its_own_delete_route_not_sliders(): void
    {
        $manager = $this->managerUser();

        $response = $this->actingAs($manager)->get(route('manager.trusteds.create'));

        $response->assertOk();
        $response->assertSee(route('manager.trusteds.thumbnails.delete', [':id']), false);
        $response->assertDontSee('sliders/delete/thumbnails');
    }

    public function test_bundles_delete_thumbnails_requires_permission(): void
    {
        $user = $this->userWithoutPermissions();

        $this->actingAs($user)
            ->deleteJson(route('manager.bundles.thumbnails.delete', ['id' => 999999]))
            ->assertForbidden();
    }

    public function test_bundles_store_thumbnails_requires_permission(): void
    {
        $user = $this->userWithoutPermissions();

        $this->actingAs($user)
            ->post(route('manager.bundles.thumbnails'), [])
            ->assertForbidden();
    }

    public function test_bundles_delete_thumbnails_does_not_error_on_missing_media(): void
    {
        $manager = $this->managerUser();

        // Antes del fix, `Media::find($id)->delete()` sin null-safe lanzaba un
        // error fatal (Call to a member function delete() on null) para un id
        // inexistente. Ahora debe responder success sin romper.
        $this->actingAs($manager)
            ->deleteJson(route('manager.bundles.thumbnails.delete', ['id' => 999999]))
            ->assertOk()
            ->assertJson(['status' => 'success']);
    }

    public function test_bundles_delete_thumbnails_only_deletes_its_own_media(): void
    {
        $manager = $this->managerUser();
        $bundle = Bundle::create([
            'slack' => (string) Str::uuid(),
            'title' => 'Bundle de prueba',
            'slug' => 'bundle-de-prueba-'.Str::random(8),
            'description' => 'Descripcion de prueba',
            'price' => 100,
            'available' => 1,
        ]);
        $bundle->addMediaFromString('fake-image-content')
            ->usingFileName('thumbnail.png')
            ->toMediaCollection('thumbnail');

        $media = $bundle->getMedia('thumbnail')->first();

        $this->actingAs($manager)
            ->deleteJson(route('manager.bundles.thumbnails.delete', ['id' => $media->id]))
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertModelMissing($media);
    }
}
