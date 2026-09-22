<?php

namespace Tests\Feature\Managers\Mailer;

use App\Models\Mailer\MailerTemplate;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura de MailerTemplateController::update(), incluyendo la regresión
 * del recorte de versiones antiguas: `->skip($maxVersions)->pluck('id')`
 * sin `->take()` compila a un `OFFSET` sin `LIMIT`, sintaxis inválida en
 * MySQL/MariaDB — rompía el guardado de CUALQUIER plantilla, siempre,
 * incluso con cero versiones previas.
 */
class MailerTemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    public function test_manager_can_update_template(): void
    {
        $template = MailerTemplate::create([
            'key' => 'QA_TEMPLATE_TEST',
            'name' => 'QA Template Test',
            'subject' => 'Asunto original',
            'content' => '<p>Original</p>',
            'module' => 'core',
            'is_enabled' => true,
            'is_protected' => false,
        ]);

        $this->actingAs($this->manager)
            ->patch(route('mailers.templates.update', $template->uid), [
                'subject' => 'Asunto actualizado',
                'content' => '<p>Actualizado</p>',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('mailer_templates', [
            'id' => $template->id,
            'subject' => 'Asunto actualizado',
        ]);
    }

    public function test_update_creates_a_version_and_trims_old_ones(): void
    {
        $template = MailerTemplate::create([
            'key' => 'QA_TEMPLATE_TRIM',
            'name' => 'QA Template Trim',
            'subject' => 'Asunto original',
            'content' => '<p>Original</p>',
            'module' => 'core',
            'is_enabled' => true,
            'is_protected' => false,
        ]);

        config(['mailer-module.retention.versions_per_template' => 2]);

        foreach (range(1, 4) as $i) {
            $this->actingAs($this->manager)
                ->patch(route('mailers.templates.update', $template->uid), [
                    'subject' => "Asunto v{$i}",
                    'content' => "<p>v{$i}</p>",
                ])
                ->assertRedirect();
        }

        $this->assertSame(2, $template->versions()->count());
    }
}
