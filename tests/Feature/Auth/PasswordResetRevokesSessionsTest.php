<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

/**
 * Restablecer la contraseña tiene que dejar fuera a quien ya estuviera dentro.
 *
 * El proyecto guarda en `users.session` el id de la sesión con la que el
 * usuario entró, y `CheckSession` expulsa a quien no coincida ("el último login
 * gana"). El restablecimiento, en cambio, llamaba a `$user->sessions()->delete()`
 * —filas de la tabla `sessions`—, y SESSION_DRIVER es `file` desde hace tiempo:
 * esa tabla solo guarda fósiles de octubre de 2025. El borrado no cerraba nada
 * y no tocaba `users.session`, así que la sesión abierta seguía siendo válida
 * hasta que la víctima volviera a iniciar sesión.
 *
 * Es justo el caso que importa: se restablece la contraseña precisamente cuando
 * se sospecha que alguien ha entrado en la cuenta.
 */
class PasswordResetRevokesSessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_revoking_clears_the_stored_session_id(): void
    {
        $user = User::factory()->create(['session' => 'id-de-sesion-abierta']);

        revokeUserSessions($user);

        $this->assertNull(
            $user->session,
            'CheckSession compara contra users.session: si no se limpia, la sesión abierta sigue valiendo.'
        );
    }

    public function test_revoking_destroys_the_session_in_the_active_driver(): void
    {
        $handler = Session::getHandler();
        $sessionId = 'sesion-de-prueba-'.uniqid();
        $handler->write($sessionId, 'contenido');

        $this->assertNotEmpty($handler->read($sessionId), 'La sesión debería existir antes de revocarla.');

        $user = User::factory()->create(['session' => $sessionId]);
        revokeUserSessions($user);

        $this->assertEmpty(
            $handler->read($sessionId),
            'Hay que destruirla en el driver activo (file), no borrar filas de la tabla sessions.'
        );
    }

    public function test_revoking_a_user_without_an_open_session_is_harmless(): void
    {
        $user = User::factory()->create(['session' => null]);

        revokeUserSessions($user);

        $this->assertNull($user->session);
    }

    public function test_password_reset_revokes_the_open_session(): void
    {
        $handler = Session::getHandler();
        $sessionId = 'sesion-del-atacante-'.uniqid();
        $handler->write($sessionId, 'contenido');

        $user = User::factory()->create([
            'session' => $sessionId,
            'password_reset_token' => 'un-token-valido',
            'password_reset_last_tried_on' => now(),
        ]);

        $this->post(route('password.update'), [
            'slack' => $user->slack,
            'token' => 'un-token-valido',
            'password' => 'Contrasena-2026-larga',
            'password_confirmation' => 'Contrasena-2026-larga',
        ])->assertOk();

        $this->assertNull($user->fresh()->session, 'Tras restablecer, la sesión guardada debe desaparecer.');
        $this->assertEmpty($handler->read($sessionId), 'Y la sesión real debe quedar destruida.');
    }
}
