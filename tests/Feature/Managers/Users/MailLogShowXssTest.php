<?php

namespace Tests\Feature\Managers\Users;

use App\Models\MailLog;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MailLog::body_html es el HTML EXACTO de cualquier correo saliente,
 * capturado indiscriminadamente por LogMailSent (incluye plantillas/campañas
 * editables por managers). Antes se inyectaba con {!! !!} directo en el DOM
 * del panel; ahora va aislado en un <iframe sandbox="allow-same-origin"> (sin
 * allow-scripts) vía srcdoc, para que un <script> embebido no se ejecute.
 */
class MailLogShowXssTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_page_renders_without_error(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $manager = User::factory()->manager()->create();

        $log = MailLog::create([
            'recipient_email' => 'destinatario@example.com',
            'subject' => 'Asunto de prueba',
            'body_html' => '<p>Contenido normal del correo</p>',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($manager)
            ->get(route('manager.users.emails.show', $log->id))
            ->assertOk();
    }

    public function test_malicious_body_html_is_not_injected_into_the_panel_dom(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $manager = User::factory()->manager()->create();

        $log = MailLog::create([
            'recipient_email' => 'destinatario@example.com',
            'subject' => 'Asunto de prueba',
            'body_html' => '<script>alert(document.cookie)</script>',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($manager)
            ->get(route('manager.users.emails.show', $log->id))
            ->assertOk();

        // El <script> del correo NO debe aparecer como una etiqueta <script>
        // real en el documento del panel -- solo dentro del atributo srcdoc
        // del iframe (donde sandbox="allow-same-origin" sin allow-scripts
        // impide que se ejecute).
        $response->assertDontSee('<script>alert(document.cookie)</script>', false);
        $response->assertSee('sandbox="allow-same-origin"', false);
    }
}
