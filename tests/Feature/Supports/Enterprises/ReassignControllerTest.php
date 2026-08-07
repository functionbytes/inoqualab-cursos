<?php

namespace Tests\Feature\Supports\Enterprises;

use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: reassignAll() llamaba a User::identification($identification)
 * dentro de un foreach masivo. Ese scope aborta con 404 si no hay match --
 * eso hacía que el chequeo "if (! $user || ! $newEnterprise)" ya escrito en
 * el método (con el JSON de error que identifica cuál falló) fuera código
 * MUERTO inalcanzable: una identificación con typo abortaba el request
 * COMPLETO con un 404 crudo antes de llegar a ese chequeo, y las
 * reasignaciones ya guardadas (sin transacción) quedaban hechas a medias.
 */
class ReassignControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $support;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->support = User::factory()->create(['role' => 'support']);
        $this->support->syncRoles(['support']);
    }

    public function test_a_nonexistent_identification_returns_a_graceful_error_not_a_404(): void
    {
        $enterprise = Enterprise::factory()->create();

        $this->actingAs($this->support)
            ->postJson(route('support.enterprises.users.reassign.all'), [
                'enterprise' => $enterprise->slack,
                // 'NO-EXISTE' no pertenece a ningún usuario -- antes del fix,
                // esto abortaba con 404 crudo en vez del JSON de error ya escrito.
                'users' => 'NO-EXISTE',
            ])
            ->assertOk()
            ->assertJson(['success' => false, 'message' => 'Usuario o empresa no encontrados.']);
    }

    public function test_reassigns_a_valid_user(): void
    {
        $oldEnterprise = Enterprise::factory()->create();
        $newEnterprise = Enterprise::factory()->create();

        $user = User::factory()->create(['identification' => 'VALID123']);
        EnterpriseUser::create([
            'user_id' => $user->id,
            'enterprise_id' => $oldEnterprise->id,
            'available' => 1,
        ]);

        $this->actingAs($this->support)
            ->postJson(route('support.enterprises.users.reassign.all'), [
                'enterprise' => $newEnterprise->slack,
                'users' => 'VALID123',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('enterprise_user', [
            'user_id' => $user->id,
            'enterprise_id' => $newEnterprise->id,
        ]);
    }
}
