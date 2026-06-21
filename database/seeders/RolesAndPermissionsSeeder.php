<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Siembra los 6 roles del sistema y un catálogo de permisos granulares
 * `{alias}.{action}` siguiendo la convención del proyecto.
 *
 * Coexiste con la columna `role` de `users`: los nombres de rol coinciden
 * exactamente con los valores de esa columna, de modo que el observer de
 * sincronización pueda asignar el rol Spatie correcto. Idempotente.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /** Acciones estándar por entidad. */
    private const ACTIONS = ['view', 'create', 'update', 'delete', 'manage'];

    /** Catálogo de entidades (alias de permiso) del dominio LMS + e-commerce. */
    private const ENTITIES = [
        'courses', 'lessons', 'quizzes', 'exams', 'bundles',
        'orders', 'invoices', 'inscriptions', 'certificates', 'certifiers',
        'coupons', 'users', 'enterprises', 'distributors', 'staff',
        'registers', 'blogs', 'faqs', 'testimonies', 'departments',
        'newsletters', 'incoming-mails', 'contacts', 'documents', 'instructions',
        'analytics', 'roles', 'certifications', 'categories', 'reviews',
        'sliders', 'trusteds', 'seo',
    ];

    /**
     * Permisos concedidos por rol (además de los implícitos del manager,
     * que recibe TODO). Se expande `entity.*` a las 5 acciones.
     */
    private const ROLE_GRANTS = [
        'accounting' => [
            'invoices.*', 'orders.*', 'distributors.view', 'distributors.update',
            'enterprises.view', 'enterprises.update', 'analytics.view',
        ],
        'support' => [
            'users.*', 'contacts.*', 'faqs.*', 'instructions.*', 'documents.*',
            'incoming-mails.*', 'departments.view', 'inscriptions.view',
            'distributors.*', 'enterprises.*', 'settings.view', 'settings.update',
        ],
        'distributor' => [
            'courses.view', 'inscriptions.*', 'enterprises.*', 'staff.*',
            'registers.*', 'orders.view', 'invoices.view',
            'settings.view', 'settings.update',
        ],
        'enterprise' => [
            'courses.view', 'users.view', 'users.update', 'inscriptions.view',
            'certificates.view', 'staff.view', 'documents.view', 'enterprises.view',
        ],
        'customer' => [
            'courses.view', 'orders.view', 'invoices.view', 'quizzes.view',
            'exams.view', 'certificates.view', 'inscriptions.view',
        ],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Crear todos los permisos del catálogo.
        foreach (self::ENTITIES as $entity) {
            foreach (self::ACTIONS as $action) {
                Permission::firstOrCreate([
                    'name' => "{$entity}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Permisos de settings (transversales).
        foreach (['view', 'update'] as $action) {
            Permission::firstOrCreate(['name' => "settings.{$action}", 'guard_name' => 'web']);
        }

        // 2. Crear los roles (nombres == valores de la columna `role`).
        $roles = [];
        foreach (['superadmin', 'manager', 'customer', 'support', 'distributor', 'enterprise', 'accounting'] as $name) {
            $roles[$name] = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // 3. superadmin → TODOS los permisos (super administrador).
        $roles['superadmin']->syncPermissions(Permission::all());

        // 4. manager → todo EXCEPTO la gestión de roles/permisos (exclusiva de superadmin).
        $roles['manager']->syncPermissions(
            Permission::where('name', 'not like', 'roles.%')->get()
        );

        // 5. Resto de roles → subconjunto declarado.
        foreach (self::ROLE_GRANTS as $roleName => $grants) {
            $permissions = $this->expandGrants($grants);
            $roles[$roleName]->syncPermissions($permissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info('Roles y permisos sembrados: '
            .Role::count().' roles, '.Permission::count().' permisos.');
    }

    /**
     * Expande patrones `entity.*` a sus 5 acciones; deja intactos los `entity.action`.
     *
     * @param  array<int, string>  $grants
     * @return array<int, string>
     */
    private function expandGrants(array $grants): array
    {
        $names = [];

        foreach ($grants as $grant) {
            if (str_ends_with($grant, '.*')) {
                $entity = substr($grant, 0, -2);
                foreach (self::ACTIONS as $action) {
                    $names[] = "{$entity}.{$action}";
                }
            } else {
                $names[] = $grant;
            }
        }

        // Solo conceder permisos que existan en el catálogo.
        return Permission::whereIn('name', array_unique($names))
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();
    }
}
