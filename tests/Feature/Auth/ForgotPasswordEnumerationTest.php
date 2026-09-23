<?php

namespace Tests\Feature\Auth;

use App\Events\Auth\Password\ForgotPasswordCreated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Regresión: sendResetLinkEmail() mostraba un error específico ("El correo o
 * cedula no coincide con nuestros registros.") solo cuando el email/cédula no
 * existía -- un atacante podía enumerar cuentas reales probando direcciones,
 * exactamente el vector que LoginController::sendFailedLoginResponse() ya
 * evita a propósito (mismo comentario en ese controller). Ahora ambos casos
 * devuelven la misma pantalla de éxito.
 */
class ForgotPasswordEnumerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([ForgotPasswordCreated::class]);
    }

    public function test_unknown_email_gets_the_same_success_screen_as_a_real_one(): void
    {
        $user = User::factory()->create(['email' => 'real@example.com']);

        $realResponse = $this->post('/password/email', ['email' => 'real@example.com']);
        $fakeResponse = $this->post('/password/email', ['email' => 'no-existe@example.com']);

        $realResponse->assertOk()->assertViewIs('auth.passwords.success');
        $fakeResponse->assertOk()->assertViewIs('auth.passwords.success');

        Event::assertDispatched(ForgotPasswordCreated::class, fn ($event) => $event->user->is($user));
        Event::assertDispatchedTimes(ForgotPasswordCreated::class, 1);
    }

    public function test_unknown_email_does_not_create_or_modify_any_user(): void
    {
        $this->post('/password/email', ['email' => 'nadie@example.com']);

        $this->assertDatabaseMissing('users', ['email' => 'nadie@example.com']);
    }

    public function test_known_email_still_generates_a_reset_token(): void
    {
        $user = User::factory()->create(['email' => 'real2@example.com']);
        $this->assertNull($user->password_reset_token);

        $this->post('/password/email', ['email' => 'real2@example.com']);

        $this->assertNotNull($user->fresh()->password_reset_token);
    }
}
