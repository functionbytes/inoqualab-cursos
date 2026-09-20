<?php

namespace Tests\Feature\Supports\Enterprises;

use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regresión de UserController::check() (mismo código duplicado en
 * Supports\Users\ManagementController::check(), sin ruta -- código muerto,
 * arreglado por consistencia pero sin test HTTP posible):
 *
 * 1. El "if ($enterprise)" no tenía "else": una fila enterprise_user
 *    huérfana (enterprise_id sin fila en enterprises) hacía que el método
 *    cayera al final sin ningún return -- respuesta vacía en vez de JSON.
 * 2. $distributor->title sin proteger: distributor_id huérfano revienta.
 * 3. route('distributor.supports.users', ...) no existe como ruta --
 *    RouteNotFoundException garantizada en el caso "feliz" (completamente
 *    asignado), el más común.
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->support = User::factory()->create(['role' => 'support']);
    }

    public function test_check_returns_json_when_fully_assigned_to_a_distributor(): void
    {
        $distributor = Distributor::factory()->create();
        $enterprise = Enterprise::factory()->create();
        DistributorEnterprise::create([
            'distributor_id' => $distributor->id,
            'enterprise_id' => $enterprise->id,
            'available' => 1,
        ]);

        $customer = User::factory()->create(['role' => 'customer', 'identification' => 'ID-12345']);
        EnterpriseUser::create([
            'user_id' => $customer->id,
            'enterprise_id' => $enterprise->id,
            'available' => 1,
        ]);

        // Antes: route('distributor.supports.users', ...) no existe ->
        // RouteNotFoundException justo en el caso más común (todo asignado).
        $this->actingAs($this->support)
            ->postJson(route('support.enterprises.users.check'), ['identification' => 'ID-12345'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('url', route('support.enterprises.users', ['slack' => $enterprise->slack]));
    }

    public function test_check_returns_json_when_the_enterprise_was_soft_deleted(): void
    {
        $enterprise = Enterprise::factory()->create();
        $customer = User::factory()->create(['role' => 'customer', 'identification' => 'ID-99999']);
        EnterpriseUser::create([
            'user_id' => $customer->id,
            'enterprise_id' => $enterprise->id,
            'available' => 1,
        ]);
        $enterprise->delete(); // soft delete: enterprise_user queda huérfana

        // Antes: el "if ($enterprise)" sin "else" dejaba caer el método sin
        // ningún return -- respuesta vacía en vez de un JSON con 'success'.
        $this->actingAs($this->support)
            ->postJson(route('support.enterprises.users.check'), ['identification' => 'ID-99999'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('enterprise', null);
    }

    public function test_update_rejects_a_target_user_with_a_non_manageable_role(): void
    {
        // update() no llamaba a guardManageableUser() (a diferencia de
        // edit()/view() en este mismo controller): un soporte podia cambiar
        // password/email de CUALQUIER usuario -- incluidos manager/support --
        // enviando su slack aqui.
        $manager = User::factory()->create(['role' => 'manager', 'firstname' => 'Intacto']);

        $this->actingAs($this->support)
            ->post(route('support.enterprises.users.update'), [
                'slack' => $manager->slack,
                'firstname' => 'Hackeado',
                'lastname' => $manager->lastname,
                'email' => $manager->email,
                'identification' => $manager->identification,
                'available' => 1,
                'password' => 'pwned12345',
            ])
            ->assertForbidden();

        $manager->refresh();
        $this->assertSame('Intacto', $manager->firstname);
        $this->assertFalse(Hash::check('pwned12345', $manager->password));
    }
}
