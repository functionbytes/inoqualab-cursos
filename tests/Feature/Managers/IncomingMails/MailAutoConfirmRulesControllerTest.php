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

    private function makeRule(int $minConfidence, bool $isActive, array $enterprise = []): MailAutoConfirmRule
    {
        return MailAutoConfirmRule::create([
            'enterprise_id' => Enterprise::factory()->create($enterprise)->id,
            'min_confidence' => $minConfidence,
            'is_active' => $isActive,
        ]);
    }

    public function test_index_shows_rules_and_stats(): void
    {
        $this->makeRule(90, true);
        $this->makeRule(70, false);

        $this->actingAs($this->supportScopedManager())
            ->get(route('manager.mails'))
            ->assertOk()
            ->assertViewIs('managers.views.mails.settings.index')
            ->assertViewHas('stats', ['total' => 2, 'active' => 1, 'inactive' => 1, 'average' => 80]);
    }

    public function test_index_ajax_returns_only_the_table_partial(): void
    {
        $this->actingAs($this->supportScopedManager())
            ->get(route('manager.mails'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertViewIs('managers.views.mails.settings._table');
    }

    public function test_index_filters_by_status_confidence_and_enterprise(): void
    {
        $actor = $this->supportScopedManager();
        $match = $this->makeRule(75, true, ['title' => 'Lacteos del Norte']);
        $this->makeRule(95, true, ['title' => 'Lacteos del Sur']);
        $this->makeRule(75, false, ['title' => 'Lacteos del Este']);

        $response = $this->actingAs($actor)
            ->get(route('manager.mails', ['search' => 'Lacteos', 'status' => '1', 'confidence' => 'medium']))
            ->assertOk();

        $this->assertSame([$match->id], $response->viewData('rules')->pluck('id')->all());
    }

    public function test_bulk_action_deactivates_selected_rules(): void
    {
        $first = $this->makeRule(90, true);
        $second = $this->makeRule(90, true);
        $untouched = $this->makeRule(90, true);

        $this->actingAs($this->supportScopedManager())
            ->postJson(route('manager.mails.rules.bulk-action'), [
                'action' => 'deactivate',
                'ids' => [$first->id, $second->id],
            ])
            ->assertOk()
            ->assertJsonPath('count', 2);

        $this->assertDatabaseHas('mail_auto_confirm_rules', ['id' => $first->id, 'is_active' => false]);
        $this->assertDatabaseHas('mail_auto_confirm_rules', ['id' => $second->id, 'is_active' => false]);
        $this->assertDatabaseHas('mail_auto_confirm_rules', ['id' => $untouched->id, 'is_active' => true]);
    }

    public function test_bulk_action_deletes_selected_rules(): void
    {
        $rule = $this->makeRule(90, true);

        $this->actingAs($this->supportScopedManager())
            ->postJson(route('manager.mails.rules.bulk-action'), ['action' => 'delete', 'ids' => [$rule->id]])
            ->assertOk();

        $this->assertDatabaseMissing('mail_auto_confirm_rules', ['id' => $rule->id]);
    }

    public function test_bulk_action_rejects_an_unknown_action(): void
    {
        $rule = $this->makeRule(90, true);

        $this->actingAs($this->supportScopedManager())
            ->postJson(route('manager.mails.rules.bulk-action'), ['action' => 'archive', 'ids' => [$rule->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('action');
    }
}
