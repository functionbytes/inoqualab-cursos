<?php

namespace Tests\Feature\Authorization;

use App\Models\Inscription;
use App\Models\Order\Order;
use App\Models\User;
use App\Models\Users\Certificate;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cubre la fundación de autorización en coexistencia (columna role <-> Spatie)
 * y las Policies de las entidades sensibles. Fija el comportamiento seguro
 * para permitir el despliegue gradual del enforcement sin regresiones.
 */
class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    // ── Coexistencia columna role <-> Spatie ──────────────────────────────

    public function test_creating_user_with_role_column_assigns_spatie_role(): void
    {
        $manager = User::factory()->manager()->create();

        $this->assertTrue($manager->hasRole('manager'));
        $this->assertTrue($manager->can('invoices.delete'));
        $this->assertTrue($manager->can('orders.manage'));
    }

    public function test_customer_role_has_only_scoped_permissions(): void
    {
        $customer = User::factory()->customer()->create();

        $this->assertTrue($customer->hasRole('customer'));
        $this->assertTrue($customer->can('courses.view'));
        $this->assertTrue($customer->can('orders.view'));
        $this->assertFalse($customer->can('invoices.delete'));
        $this->assertFalse($customer->can('users.delete'));
    }

    public function test_changing_role_column_resyncs_spatie_role(): void
    {
        $user = User::factory()->customer()->create();
        $this->assertTrue($user->hasRole('customer'));

        $user->update(['role' => 'manager']);

        $this->assertTrue($user->fresh()->hasRole('manager'));
        $this->assertFalse($user->fresh()->hasRole('customer'));
    }

    public function test_legacy_role_value_does_not_break_creation(): void
    {
        // 'student' no está en el catálogo sembrado: el observer lo ignora
        // en vez de fallar, y hasRole() (trait HasRoles) devuelve false.
        $user = User::factory()->role('student')->create();

        $this->assertFalse($user->hasRole('student'));
        $this->assertInstanceOf(User::class, $user);
    }

    public function test_all_roles_are_seeded(): void
    {
        // superadmin (todos los permisos) + los 6 roles de dominio.
        $this->assertEqualsCanonicalizing(
            ['superadmin', 'manager', 'customer', 'support', 'distributor', 'enterprise', 'accounting'],
            Role::pluck('name')->all()
        );
    }

    public function test_new_panel_domains_have_granular_permissions(): void
    {
        // Dominios del panel manager que antes quedaban en fail-open por no
        // tener permiso en el catálogo. Ahora deben existir y respetar la
        // granularidad: manager los tiene, customer no.
        $manager = User::factory()->manager()->create();
        $customer = User::factory()->customer()->create();

        foreach (['seo', 'reviews', 'sliders', 'trusteds', 'categories', 'certifications'] as $domain) {
            $this->assertTrue(
                Permission::where('name', "{$domain}.view")->exists(),
                "falta el permiso {$domain}.view en el catálogo"
            );
            $this->assertTrue($manager->can("{$domain}.view"), "manager debería poder ver {$domain}");
            $this->assertFalse($customer->can("{$domain}.view"), "customer NO debería poder ver {$domain}");
        }
    }

    // ── OrderPolicy (ownership por user_id) ───────────────────────────────

    public function test_order_owner_can_view_others_cannot(): void
    {
        $owner = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $manager = User::factory()->manager()->create();

        $order = new Order(['user_id' => $owner->id]);

        $this->assertTrue($owner->can('view', $order), 'el dueño puede ver');
        $this->assertFalse($other->can('view', $order), 'otro cliente NO puede ver (IDOR)');
        $this->assertTrue($manager->can('view', $order), 'manager bypassa por orders.manage');
    }

    public function test_customer_cannot_delete_orders(): void
    {
        $owner = User::factory()->customer()->create();
        $order = new Order(['user_id' => $owner->id]);

        // customer no tiene orders.delete
        $this->assertFalse($owner->can('delete', $order));
    }

    // ── CertificatePolicy / InscriptionPolicy ─────────────────────────────

    public function test_certificate_and_inscription_ownership_is_enforced(): void
    {
        $owner = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();

        $certificate = new Certificate(['user_id' => $owner->id]);
        $inscription = new Inscription(['user_id' => $owner->id]);

        $this->assertTrue($owner->can('view', $certificate));
        $this->assertFalse($other->can('view', $certificate));

        $this->assertTrue($owner->can('view', $inscription));
        $this->assertFalse($other->can('view', $inscription));
    }

    // ── UserPolicy (self vs manage) ───────────────────────────────────────

    public function test_user_can_manage_self_but_not_delete_self(): void
    {
        $user = User::factory()->customer()->create();

        $this->assertTrue($user->can('view', $user));
        $this->assertTrue($user->can('update', $user));
        $this->assertFalse($user->can('delete', $user), 'nadie se elimina a sí mismo');
    }

    public function test_support_can_manage_other_users(): void
    {
        $support = User::factory()->support()->create();
        $target = User::factory()->customer()->create();

        $this->assertTrue($support->can('update', $target));
        $this->assertTrue($support->can('delete', $target));
    }
}
