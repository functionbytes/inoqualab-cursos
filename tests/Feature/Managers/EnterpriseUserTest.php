<?php

namespace Tests\Feature\Managers;

use App\Models\Enterprise\Enterprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function createEnterprise(): Enterprise
    {
        return Enterprise::create([
            'slack' => (string) Str::uuid(),
            'title' => 'Empresa de Prueba S.A.',
            'available' => 1,
        ]);
    }

    public function test_enterprise_role_user_gets_enterprise_id_assigned(): void
    {
        // Regresión: Managers/Users/UsersController::store() comparaba
        // $request->roles === 'enterprises' (typo en campo y valor plural),
        // por lo que enterprise_id quedaba siempre en null al crear un usuario
        // con role='enterprise'.
        $manager = User::factory()->manager()->create();
        $enterprise = $this->createEnterprise();

        $this->actingAs($manager)
            ->post(route('manager.users.store'), [
                'firstname' => 'Juan',
                'lastname' => 'Perez',
                'email' => 'juan.perez@test.com',
                'role' => 'enterprise',
                'enterprises' => $enterprise->id,
                'password' => 'password123',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $user = User::where('email', 'juan.perez@test.com')->first();

        $this->assertNotNull($user, 'El usuario enterprise debe haberse creado.');
        $this->assertSame('enterprise', $user->role);
        $this->assertSame($enterprise->id, $user->enterprise_id);
    }

    public function test_customer_role_user_does_not_get_enterprise_id(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->post(route('manager.users.store'), [
                'firstname' => 'Maria',
                'lastname' => 'Lopez',
                'email' => 'maria.lopez@test.com',
                'role' => 'customer',
                'password' => 'password123',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $user = User::where('email', 'maria.lopez@test.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('customer', $user->role);
        $this->assertNull($user->enterprise_id);
    }

    public function test_enterprise_user_password_hashed_exactly_once_via_manager_create(): void
    {
        // Regresión: Managers/Enterprises/UserController::store() también tenía
        // Hash::make() manual que causaba doble hash al crear usuarios de empresa.
        $manager = User::factory()->manager()->create();
        $enterprise = $this->createEnterprise();

        $password = 'testPassword789';

        $this->actingAs($manager)
            ->post(route('manager.users.store'), [
                'firstname' => 'Carlos',
                'lastname' => 'Ruiz',
                'email' => 'carlos.ruiz@test.com',
                'role' => 'enterprise',
                'enterprises' => $enterprise->id,
                'password' => $password,
            ])
            ->assertJson(['success' => true]);

        $user = User::where('email', 'carlos.ruiz@test.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue(
            Hash::check($password, $user->password),
            'La contraseña del usuario enterprise creado por manager debe estar hasheada una sola vez.'
        );
    }

    public function test_users_search_does_not_leak_users_from_other_enterprises(): void
    {
        // Regresión: Managers/Enterprises/UserController::index() encadenaba
        // ->where(...)->orWhere(...)->orWhere(...)->orWhere(...) sin closure,
        // lo que rompía el scope de $enterprise->users() (el where de
        // enterprise_id quedaba fuera del OR) y devolvía usuarios de TODAS
        // las empresas en cuanto la búsqueda matcheaba cualquiera de las
        // condiciones OR.
        $manager = User::factory()->manager()->create();
        $enterpriseA = $this->createEnterprise();
        $enterpriseB = $this->createEnterprise();

        $userA = User::factory()->create([
            'firstname' => 'Ana',
            'lastname' => 'Alvarez',
            'role' => 'enterprise',
            'enterprise_id' => $enterpriseA->id,
        ]);
        $userB = User::factory()->create([
            'firstname' => 'Andres',
            'lastname' => 'Bravo',
            'role' => 'enterprise',
            'enterprise_id' => $enterpriseB->id,
        ]);
        $enterpriseA->users()->attach($userA);
        $enterpriseB->users()->attach($userB);

        $response = $this->actingAs($manager)
            ->get(route('manager.enterprises.users', ['slack' => $enterpriseA->slack, 'search' => 'A']))
            ->assertOk();

        $users = $response->viewData('users');

        $this->assertTrue($users->contains('id', $userA->id), 'Debe incluir al usuario de la propia empresa que matchea la búsqueda.');
        $this->assertFalse($users->contains('id', $userB->id), 'NO debe incluir usuarios de otra empresa aunque matcheen la búsqueda.');
    }
}
