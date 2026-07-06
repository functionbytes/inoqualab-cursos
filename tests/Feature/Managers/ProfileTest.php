<?php

namespace Tests\Feature\Managers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Cubre el perfil propio del manager: ver la página, actualizar datos, cambiar
 * la contraseña (verificando la actual) y las reglas de seguridad/autorización.
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        // La contraseña se pasa en texto para que el mutator del modelo la hashee
        // UNA sola vez (el default del factory ya viene hasheado -> doble hash).
        return User::factory()->role('manager')->create(['password' => 'password']);
    }

    public function test_manager_can_view_profile_page(): void
    {
        $response = $this->actingAs($this->manager())->get(route('manager.profile.edit'));

        $response->assertOk();
        $response->assertSee('Mi perfil');
    }

    public function test_manager_can_update_profile_data(): void
    {
        $manager = $this->manager();

        $response = $this->actingAs($manager)->putJson(route('manager.profile.update'), [
            'firstname' => 'Nuevo',
            'lastname' => 'Nombre',
            'email' => 'nuevo@example.com',
            'cellphone' => '3001234567',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('users', [
            'id' => $manager->id,
            'email' => 'nuevo@example.com',
            'firstname' => 'NUEVO', // el controller normaliza a mayúsculas
        ]);
    }

    public function test_manager_can_change_password_with_correct_current(): void
    {
        $manager = $this->manager();

        $response = $this->actingAs($manager)->putJson(route('manager.profile.password'), [
            'current_password' => 'password',
            'password' => 'nuevaClave8',
            'password_confirmation' => 'nuevaClave8',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertTrue(Hash::check('nuevaClave8', $manager->fresh()->password));
    }

    public function test_password_change_fails_with_wrong_current(): void
    {
        $manager = $this->manager();

        $response = $this->actingAs($manager)->putJson(route('manager.profile.password'), [
            'current_password' => 'incorrecta',
            'password' => 'nuevaClave8',
            'password_confirmation' => 'nuevaClave8',
        ]);

        $response->assertStatus(422);
        // La contraseña no cambió.
        $this->assertTrue(Hash::check('password', $manager->fresh()->password));
    }

    public function test_new_password_must_be_confirmed(): void
    {
        $manager = $this->manager();

        $response = $this->actingAs($manager)->putJson(route('manager.profile.password'), [
            'current_password' => 'password',
            'password' => 'nuevaClave8',
            'password_confirmation' => 'noCoincide',
        ]);

        $response->assertStatus(422);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'ocupado@example.com']);
        $manager = $this->manager();

        $response = $this->actingAs($manager)->putJson(route('manager.profile.update'), [
            'firstname' => 'Xavier',
            'lastname' => 'Perez',
            'email' => 'ocupado@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_customer_cannot_access_manager_profile(): void
    {
        $customer = User::factory()->role('customer')->create();

        $response = $this->actingAs($customer)->get(route('manager.profile.edit'));

        $response->assertRedirect(); // middleware 'manager' no deja pasar
    }
}
