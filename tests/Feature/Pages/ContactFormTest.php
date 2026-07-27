<?php

namespace Tests\Feature\Pages;

use App\Mail\Pages\Contact\AlertsMails;
use App\Mail\Pages\Contact\ResponseMails;
use App\Models\Contact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * El formulario de contacto enviaba sus dos correos con Mail::send() dentro de
 * la petición del visitante. Como los mailables no implementaban ShouldQueue,
 * el envío era síncrono: quien rellenaba el formulario esperaba a que
 * respondiera el SMTP y, si fallaba, el correo se perdía dejando solo una línea
 * en el log (el try/catch evitaba el 500, pero nadie se enteraba).
 */
class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private function activarNotificaciones(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'contact_notifications'],
            ['value' => '1']
        );
    }

    private function datosValidos(): array
    {
        return [
            'firstname' => 'Ana',
            'lastname' => 'Ruiz',
            'email' => 'ana@example.com',
            'cellphone' => '3001234567',
            'message' => 'Quisiera información sobre los cursos de BPM.',
        ];
    }

    public function test_contact_mailables_are_queued(): void
    {
        // Es lo que evita que el visitante espere al SMTP. El constructor lee
        // campos del contacto, así que hace falta uno con datos.
        $contacto = new Contact([
            'slack' => 'abc123',
            'firstname' => 'Ana',
            'lastname' => 'Ruiz',
            'email' => 'ana@example.com',
            'message' => 'Hola',
        ]);
        $contacto->created_at = now();

        $this->assertInstanceOf(ShouldQueue::class, new AlertsMails($contacto));
        $this->assertInstanceOf(ShouldQueue::class, new ResponseMails($contacto));
    }

    public function test_form_stores_the_message(): void
    {
        Mail::fake();

        // El formulario es AJAX: el controller responde JSON, no redirige.
        $this->postJson(route('contacts.store'), $this->datosValidos())
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('contacts', [
            'email' => 'ana@example.com',
            'reviewed' => 0,
        ]);
    }

    public function test_form_queues_the_notification_when_enabled(): void
    {
        Mail::fake();
        $this->activarNotificaciones();

        $this->post(route('contacts.store'), $this->datosValidos());

        Mail::assertQueued(AlertsMails::class);
    }

    public function test_form_rejects_an_invalid_email(): void
    {
        Mail::fake();

        // array_merge y no +: el operador + no sobrescribe claves existentes.
        $this->post(route('contacts.store'), array_merge($this->datosValidos(), ['email' => 'no-es-correo']))
            ->assertSessionHasErrors('email');

        Mail::assertNothingQueued();
    }
}
