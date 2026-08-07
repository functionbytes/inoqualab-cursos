<?php

namespace Tests\Feature\Managers\MailTemplates;

use App\Models\MailTemplate;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Regresión: MailTemplatesController::sendTest() reflejaba test_email crudo
 * en el mensaje JSON de éxito ('Correo de prueba enviado a '.$request->test_email).
 * La regla 'email' (RFCValidation) acepta HTML en el local-part si va entre
 * comillas -- "<img src=x onerror=...>"@dominio.com pasa la validación tal
 * cual -- y la vista lo inserta con .html() sin escapar (edit.blade.php),
 * ejecutando el HTML inyectado en el navegador del manager.
 */
class SendTestMailTemplateXssTest extends TestCase
{
    use RefreshDatabase;

    public function test_response_message_escapes_html_injected_via_quoted_local_part_email(): void
    {
        Mail::fake();

        $this->seed(RolesAndPermissionsSeeder::class);
        $manager = User::factory()->manager()->create();
        $template = MailTemplate::create([
            'key' => 'test-template',
            'name' => 'Plantilla de prueba',
            'subject' => 'Asunto',
            'content' => '<p>Contenido</p>',
        ]);

        $maliciousEmail = '"<img src=x onerror=alert(1)>"@a.com';

        $response = $this->actingAs($manager)->postJson(
            route('manager.mail_templates.send_test', $template->id),
            ['test_email' => $maliciousEmail]
        );

        $response->assertOk()->assertJson(['success' => true]);

        $message = $response->json('message');
        $this->assertStringNotContainsString('<img', $message);
        $this->assertStringContainsString('&lt;img', $message);
    }
}
