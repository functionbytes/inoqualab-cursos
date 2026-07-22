<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UpgradeAccountTest extends TestCase
{
    use RefreshDatabase;

    private function makeCity(): int
    {
        $countryId = DB::table('countries')->insertGetId(['title' => 'Colombia']);
        $stateId = DB::table('states')->insertGetId(['title' => 'Cundinamarca', 'countrie_id' => $countryId]);

        return (int) DB::table('cities')->insertGetId([
            'title' => 'Bogotá',
            'state_id' => $stateId,
        ]);
    }

    private function payload(int $cityId, array $overrides = []): array
    {
        return array_merge([
            'firstname' => 'Juan',
            'lastname' => 'Pérez',
            'cellphone' => '3001234567',
            'citie' => $cityId,
            'address' => 'Calle 123 # 45-67',
            'identification' => 'CC12345678',
            'email' => 'nuevo@correo.com',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
        ], $overrides);
    }

    public function test_upgrade_persists_city_as_citie_id(): void
    {
        // Regresión: el controller guardaba 'citie' (columna inexistente) en vez de
        // 'citie_id', por lo que la ciudad se perdía silenciosamente en cada upgrade.
        $city = $this->makeCity();
        $user = User::factory()->create(['citie_id' => null, 'validation' => 0]);

        $this->actingAs($user)
            ->postJson(route('upgrade.store'), $this->payload($city))
            ->assertOk();

        $this->assertSame($city, (int) $user->fresh()->citie_id);
        $this->assertSame(1, (int) $user->fresh()->validation);
    }

    public function test_upgrade_rejects_email_belonging_to_another_user(): void
    {
        // IDOR de correo: sin unique, un upgrade podía tomar el email de otra cuenta.
        $city = $this->makeCity();
        User::factory()->create(['email' => 'ocupado@correo.com']);
        $user = User::factory()->create(['validation' => 0]);

        $this->actingAs($user)
            ->postJson(route('upgrade.store'), $this->payload($city, ['email' => 'ocupado@correo.com']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_upgrade_requires_authentication(): void
    {
        $city = $this->makeCity();

        // Sin sesión: la ruta ahora exige auth (antes Auth::user() null daba 500).
        $this->postJson(route('upgrade.store'), $this->payload($city))
            ->assertUnauthorized();
    }
}
