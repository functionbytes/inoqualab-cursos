<?php

namespace Tests\Feature\Auth;

use App\Events\Auth\Password\ResetPasswordCreated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // ResetPasswordListener (ShouldQueue+sync) intenta construir un Mailable
        // que llama a $user->full_name. Fakeamos el evento para evitar que el
        // listener corra: lo que queremos probar es el hash de la contraseña, no el correo.
        Event::fake([ResetPasswordCreated::class]);
    }

    /** Usuario con un reset pendiente válido (token + marca de tiempo dentro de la ventana). */
    private function userWithPendingReset(string $token = 'valid-reset-token-xyz'): User
    {
        $user = User::factory()->create();
        $user->password_reset_token = $token;
        $user->password_reset_last_tried_on = Carbon::now();
        $user->save();

        return $user;
    }

    public function test_password_reset_hashes_new_password_exactly_once(): void
    {
        // Regresión: ResetPasswordController asignaba Hash::make($request->password)
        // al modelo antes de que el mutator lo hasheara de nuevo. El resultado era que
        // el usuario nunca podía entrar con la contraseña recién reseteada.
        $user = $this->userWithPendingReset();
        $newPassword = 'nuevaContrasena456';

        $response = $this->post('/password/reset', [
            'slack' => $user->slack,
            'token' => 'valid-reset-token-xyz',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertOk();

        $this->assertTrue(
            Hash::check($newPassword, $user->fresh()->password),
            'Hash::check debe pasar: el mutator hashea la contraseña una vez; hacerlo manualmente antes causaría doble hash.'
        );
    }

    public function test_password_reset_clears_the_reset_token(): void
    {
        $user = $this->userWithPendingReset('token-a-limpiar');

        $this->post('/password/reset', [
            'slack' => $user->slack,
            'token' => 'token-a-limpiar',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertNull($user->fresh()->password_reset_token);
    }

    public function test_password_reset_requires_password_confirmation(): void
    {
        $user = $this->userWithPendingReset('some-token');

        $this->post('/password/reset', [
            'slack' => $user->slack,
            'token' => 'some-token',
            'password' => 'password123',
            'password_confirmation' => 'diferente456',
        ])
            ->assertSessionHasErrors('password');
    }

    public function test_password_reset_rejects_wrong_token(): void
    {
        // Vector de toma de cuenta: conocer el slack y disparar un reset NO debe
        // bastar; sin el token correcto (que solo llega por el enlace firmado) el
        // POST directo debe rechazarse y la contraseña no debe cambiar.
        $user = $this->userWithPendingReset('el-token-real');
        $originalHash = $user->fresh()->password;

        $this->post('/password/reset', [
            'slack' => $user->slack,
            'token' => 'token-inventado-por-atacante',
            'password' => 'hackeada12345',
            'password_confirmation' => 'hackeada12345',
        ])->assertRedirect(route('password.confirm'));

        $this->assertSame($originalHash, $user->fresh()->password);
        $this->assertNotNull($user->fresh()->password_reset_token);
    }

    public function test_password_reset_rejects_expired_token(): void
    {
        // Token correcto pero solicitado hace más de 24h: el enlace firmado ya
        // expiró y el POST también debe rechazarlo.
        $user = User::factory()->create();
        $user->password_reset_token = 'token-viejo';
        $user->password_reset_last_tried_on = Carbon::now()->subHours(25);
        $user->save();
        $originalHash = $user->fresh()->password;

        $this->post('/password/reset', [
            'slack' => $user->slack,
            'token' => 'token-viejo',
            'password' => 'nuevaclave12345',
            'password_confirmation' => 'nuevaclave12345',
        ])->assertRedirect(route('password.confirm'));

        $this->assertSame($originalHash, $user->fresh()->password);
    }
}
