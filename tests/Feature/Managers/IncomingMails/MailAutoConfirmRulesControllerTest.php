<?php

namespace Tests\Feature\Managers\IncomingMails;

use App\Models\Enterprise\Enterprise;
use App\Models\Mail\MailAutoConfirmRule;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: las rutas de reglas de auto-confirmación vivían bajo
 * manager.settings.mails.*. EnforcePanelPermission deriva el dominio del 2º
 * segmento del nombre de ruta ("settings"), exigiendo settings.create/delete
 * -- que el rol Spatie 'support' NO tiene (solo settings.view/update) -- en
 * vez de incoming-mails.create/delete, que el propio controller comprueba y
 * que 'support' SÍ tiene completo (incoming-mails.*). Un actor con columna
 * role=manager (pasa el middleware IsManager) pero permisos Spatie
 * restringidos a 'support' quedaba bloqueado por el middleware antes de que
 * el controller pudiera autorizarlo. Renombradas a manager.mails.* para que
 * el alias 'mails' -> 'incoming-mails' ya existente en DOMAIN_ALIASES aplique.
 */
class MailAutoConfirmRulesControllerTest extends TestCase
{
    use RefreshDatabase;

    /** Columna role=manager (pasa IsManager) pero permisos Spatie de 'support'. */
    private function supportScopedManager(): User
    {
        $user = User::factory()->manager()->create();
        $user->syncRoles(['support']);

        return $user;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_support_scoped_actor_can_create_a_rule(): void
    {
        $actor = $this->supportScopedManager();
        $enterprise = Enterprise::factory()->create();

        $this->actingAs($actor)
            ->postJson(route('manager.mails.rules.store'), [
                'enterprise_id' => $enterprise->id,
                'min_confidence' => 80,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('mail_auto_confirm_rules', [
            'enterprise_id' => $enterprise->id,
        ]);
    }

    public function test_support_scoped_actor_can_delete_a_rule(): void
    {
        $actor = $this->supportScopedManager();
        $enterprise = Enterprise::factory()->create();
        $rule = MailAutoConfirmRule::create([
            'enterprise_id' => $enterprise->id,
            'min_confidence' => 80,
            'is_active' => true,
        ]);

        $this->actingAs($actor)
            ->deleteJson(route('manager.mails.rules.destroy', $rule->id))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('mail_auto_confirm_rules', ['id' => $rule->id]);
    }
}
