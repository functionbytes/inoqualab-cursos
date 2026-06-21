<?php

namespace Tests\Feature\Managers\Mailer;

use App\Models\Mailer\MailerVariable;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura del CRUD de variables del Mailer, incluyendo la regresión del
 * estado `is_enabled` que el store forzaba siempre a true.
 */
class MailerVariableTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->manager = User::factory()->manager()->create();
    }

    public function test_manager_can_create_enabled_variable(): void
    {
        $this->actingAs($this->manager)
            ->post(route('mailers.variables.store'), [
                'key' => 'USER_NAME', 'name' => 'Nombre', 'category' => 'Usuario', 'module' => 'core', 'is_enabled' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('mailer_variables', ['key' => 'USER_NAME', 'is_enabled' => true]);
    }

    public function test_manager_can_create_disabled_variable(): void
    {
        // Regresión: el store forzaba is_enabled=true; ahora respeta el select.
        $this->actingAs($this->manager)
            ->post(route('mailers.variables.store'), [
                'key' => 'USER_AGE', 'name' => 'Edad', 'category' => 'Usuario', 'module' => 'core', 'is_enabled' => '0',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('mailer_variables', ['key' => 'USER_AGE', 'is_enabled' => false]);
    }

    public function test_store_rejects_duplicate_key_in_module(): void
    {
        MailerVariable::create([
            'key' => 'DUP', 'name' => 'X', 'category' => 'C', 'module' => 'core', 'is_system' => false, 'is_enabled' => true,
        ]);

        $this->actingAs($this->manager)
            ->post(route('mailers.variables.store'), [
                'key' => 'DUP', 'name' => 'Y', 'category' => 'C', 'module' => 'core',
            ])
            ->assertSessionHas('error');

        $this->assertSame(1, MailerVariable::where('key', 'DUP')->count());
    }

    public function test_cannot_delete_system_variable(): void
    {
        $var = MailerVariable::create([
            'key' => 'SYS', 'name' => 'S', 'category' => 'C', 'module' => 'core', 'is_system' => true, 'is_enabled' => true,
        ]);

        $this->actingAs($this->manager)
            ->delete(route('mailers.variables.destroy', $var))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('mailer_variables', ['id' => $var->id]);
    }
}
