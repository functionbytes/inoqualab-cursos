<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // El evento Registered dispara SendEmailVerificationNotification (síncrono).
        // Fake para evitar intentos reales de SMTP en entorno de test.
        Notification::fake();
    }

    public function test_registered_user_password_is_hashed_exactly_once(): void
    {
        // Regresión: RegisterController asignaba Hash::make($request->password) al
        // modelo, causando doble hash (mutator de User vuelve a hashear). El resultado
        // era que el usuario recién registrado nunca podía hacer login.
        $email = 'nuevo.usuario@example.com';
        $password = 'password123';

        $this->withoutMiddleware(ThrottleRequests::class)
            ->post('/register', [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
            ])
            ->assertRedirect(route('login'));

        $user = User::where('email', $email)->first();

        $this->assertNotNull($user, 'El usuario debe existir en la base de datos tras el registro.');
        $this->assertTrue(
            Hash::check($password, $user->password),
            'Hash::check debe pasar: la contraseña debe estar hasheada una sola vez por el mutator del modelo.'
        );
    }

    public function test_registered_user_role_is_customer(): void
    {
        $email = 'cliente@example.com';
        $password = 'password123';

        $this->withoutMiddleware(ThrottleRequests::class)
            ->post('/register', [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
            ]);

        $user = User::where('email', $email)->first();
        $this->assertSame('customer', $user?->role);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplicado@example.com']);

        $this->withoutMiddleware(ThrottleRequests::class)
            ->post('/register', [
                'email' => 'duplicado@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_registration_fails_with_mismatched_passwords(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class)
            ->post('/register', [
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'diferente456',
            ])
            ->assertSessionHasErrors('password');
    }
}
