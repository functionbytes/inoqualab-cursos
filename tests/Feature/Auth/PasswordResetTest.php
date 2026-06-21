<?php

namespace Tests\Feature\Auth;

use App\Events\Auth\Password\ResetPasswordCreated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_password_reset_hashes_new_password_exactly_once(): void
    {
        // Regresión: ResetPasswordController asignaba Hash::make($request->password)
        // al modelo antes de que el mutator lo hasheara de nuevo. El resultado era que
        // el usuario nunca podía entrar con la contraseña recién reseteada.
        $user = User::factory()->create();
        $user->password_reset_token = 'valid-reset-token-xyz';
        $user->save();

        $newPassword = 'nuevaContrasena456';

        $response = $this->post('/password/reset', [
            'slack' => $user->slack,
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
        $user = User::factory()->create();
        $user->password_reset_token = 'token-a-limpiar';
        $user->save();

        $this->post('/password/reset', [
            'slack' => $user->slack,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertNull($user->fresh()->password_reset_token);
    }

    public function test_password_reset_requires_password_confirmation(): void
    {
        $user = User::factory()->create();
        $user->password_reset_token = 'some-token';
        $user->save();

        $this->post('/password/reset', [
            'slack' => $user->slack,
            'password' => 'password123',
            'password_confirmation' => 'diferente456',
        ])
            ->assertSessionHasErrors('password');
    }
}
