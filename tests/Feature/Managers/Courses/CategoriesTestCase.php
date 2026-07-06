<?php

namespace Tests\Feature\Managers\Courses;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Helpers compartidos para los tests de Categorías y Reseñas de curso: crea
 * un manager con los permisos Spatie sembrados (rol `manager` recibe todo
 * salvo roles.*) o un manager sin ningún permiso, para probar el gap de
 * autorización. `RefreshDatabase` se declara aquí y es heredado por las
 * subclases (Laravel resuelve los traits de test recorriendo la jerarquía).
 */
abstract class CategoriesTestCase extends TestCase
{
    use RefreshDatabase;

    protected function managerWithPermissions(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        return User::factory()->manager()->create();
    }

    protected function managerWithoutPermissions(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->manager()->create();
        $user->syncRoles([]);
        $user->syncPermissions([]);

        return $user;
    }
}
