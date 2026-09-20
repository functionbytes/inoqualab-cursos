<?php

namespace Tests\Feature\Managers\Enterprises;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_update_rejects_a_target_user_with_a_non_manageable_role(): void
    {
        // update() no llamaba a ningun guard de rol del usuario objetivo:
        // un manager con solo el permiso enterprises.update podia cambiar
        // password/email de CUALQUIER usuario -- incluidos otros manager/
        // support -- enviando su slack aqui.
        $actor = User::factory()->manager()->create();
        $target = User::factory()->create(['role' => 'support', 'firstname' => 'Intacto']);

        $this->actingAs($actor)
            ->post(route('manager.enterprises.users.update'), [
                'slack' => $target->slack,
                'firstname' => 'Hackeado',
                'lastname' => $target->lastname,
                'email' => $target->email,
                'identification' => $target->identification,
                'available' => 1,
                'password' => 'pwned12345',
            ])
            ->assertForbidden();

        $target->refresh();
        $this->assertSame('Intacto', $target->firstname);
        $this->assertFalse(Hash::check('pwned12345', $target->password));
    }
}
