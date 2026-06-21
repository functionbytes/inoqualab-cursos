<?php

namespace Tests\Feature\Managers\Mailer;

use App\Models\Mailer\MailerLayout;
use App\Models\Mailer\MailerTemplate;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Verifica que las operaciones de ESCRITURA del Mailer (templates, componentes,
 * endpoints) exigen permiso de escritura y no basta con `newsletters.view`.
 * Cierra la escalada del grupo `mailers.*`, cuyo middleware solo exige `view`.
 */
class MailerAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    /** Usuario con rol de panel manager (pasa IsManager) pero solo `newsletters.view`. */
    private function viewer(): User
    {
        $viewer = User::factory()->manager()->create();
        $viewer->syncRoles([]);
        $viewer->givePermissionTo('newsletters.view');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $viewer->fresh();
    }

    private function template(): MailerTemplate
    {
        return MailerTemplate::create([
            'uid' => (string) Str::uuid(),
            'key' => 'TEST_KEY',
            'name' => 'Plantilla',
            'subject' => 'Asunto',
            'content' => '<p>x</p>',
            'module' => 'core',
            'is_enabled' => true,
            'is_protected' => false,
        ]);
    }

    public function test_template_store_requires_create_permission(): void
    {
        $this->actingAs($this->viewer())
            ->post(route('mailers.templates.store'), [
                'key' => 'NEW_KEY', 'name' => 'N', 'subject' => 'S', 'module' => 'core',
            ])
            ->assertForbidden();
    }

    public function test_template_destroy_requires_delete_permission(): void
    {
        $template = $this->template();

        $this->actingAs($this->viewer())
            ->delete(route('mailers.templates.destroy', $template->uid))
            ->assertForbidden();

        $this->assertDatabaseHas('mailer_templates', ['id' => $template->id]);
    }

    public function test_template_bulk_action_requires_update_permission(): void
    {
        $template = $this->template();

        $this->actingAs($this->viewer())
            ->postJson(route('mailers.templates.bulk-action'), ['action' => 'delete', 'ids' => [$template->id]])
            ->assertForbidden();
    }

    public function test_component_destroy_requires_delete_permission(): void
    {
        $component = MailerLayout::create([
            'uid' => (string) Str::uuid(),
            'name' => 'Layout',
            'alias' => 'layout_'.Str::random(4),
            'type' => 'layout',
            'content' => '<html>{{ content }}</html>',
            'is_enabled' => true,
            'is_protected' => false,
        ]);

        $this->actingAs($this->viewer())
            ->delete(route('mailers.components.destroy', $component->uid))
            ->assertForbidden();

        $this->assertDatabaseHas('mailer_layouts', ['id' => $component->id]);
    }

    public function test_manager_can_destroy_template(): void
    {
        // Caso positivo: el flujo normal del manager (con permisos) no se rompe.
        $template = $this->template();

        $this->actingAs($this->manager)
            ->delete(route('mailers.templates.destroy', $template->uid))
            ->assertRedirect();

        $this->assertDatabaseMissing('mailer_templates', ['id' => $template->id]);
    }
}
