<?php

namespace Tests\Feature\Distributors\Enterprises;

use App\Models\Distributor\Distributor;
use App\Models\Distributor\DistributorEnterprise;
use App\Models\Enterprise\Enterprise;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresión: store() asignaba $user->password = $request->password sin el
 * guard filled()/Str::random() que sí tienen sus hermanos (RegistersController,
 * Enterprises/UserController) y que el propio update() de este controller ya
 * usa. El mutator de User::password() espera un string -- un password
 * ausente/vacío (ConvertEmptyStringsToNull lo deja null) tiraba un TypeError
 * crudo (500) en vez de generar una contraseña aleatoria como los flujos
 * hermanos.
 */
class StaffControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $distributorStaff;

    private Distributor $distributor;

    private Enterprise $enterprise;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->distributor = Distributor::factory()->create();
        $this->distributorStaff = User::factory()->create(['role' => 'distributor']);
        DB::table('distributor_staff')->insert([
            'distributor_id' => $this->distributor->id,
            'user_id' => $this->distributorStaff->id,
        ]);

        $this->enterprise = Enterprise::factory()->create();
        DistributorEnterprise::create([
            'distributor_id' => $this->distributor->id,
            'enterprise_id' => $this->enterprise->id,
            'available' => 1,
        ]);
    }

    public function test_store_without_password_generates_a_random_one_instead_of_crashing(): void
    {
        $this->actingAs($this->distributorStaff)
            ->postJson(route('distributor.enterprises.staffs.store'), [
                'enterprise' => $this->enterprise->slack,
                'firstname' => 'Ana',
                'lastname' => 'Gomez',
                'identification' => 'STAFF-NO-PASS',
                'email' => 'ana.staff@example.com',
                'cellphone' => '3000000000',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $user = User::where('identification', 'STAFF-NO-PASS')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->password);
    }

    public function test_store_with_explicit_password_uses_it(): void
    {
        $this->actingAs($this->distributorStaff)
            ->postJson(route('distributor.enterprises.staffs.store'), [
                'enterprise' => $this->enterprise->slack,
                'firstname' => 'Luis',
                'lastname' => 'Perez',
                'identification' => 'STAFF-WITH-PASS',
                'email' => 'luis.staff@example.com',
                'cellphone' => '3000000001',
                'password' => 'clave-explicita-123',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $user = User::where('identification', 'STAFF-WITH-PASS')->first();
        $this->assertTrue(\Hash::check('clave-explicita-123', $user->password));
    }
}
