<?php

namespace App\Observers\User;

use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Puente de coexistencia entre la columna `role` (legacy) y los roles de
 * Spatie. Mantiene el rol Spatie del usuario sincronizado con su columna,
 * de modo que el flujo de auth/registro existente (que escribe `role`)
 * siga funcionando sin cambios y a la vez alimente el RBAC nuevo.
 *
 * `syncRoles()` solo escribe tablas pivote (no la columna `role`), por lo
 * que no reentra en el observer.
 */
class UserRoleObserver
{
    public function created(User $user): void
    {
        $this->syncRoleFromColumn($user);
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('role')) {
            $this->syncRoleFromColumn($user);
        }
    }

    private function syncRoleFromColumn(User $user): void
    {
        $role = $user->role;

        if (blank($role)) {
            return;
        }

        // Solo sincronizar si el rol existe en Spatie (evita valores legacy
        // como 'student' que no forman parte del catálogo sembrado).
        if (Role::where('name', $role)->where('guard_name', 'web')->exists()) {
            $user->syncRoles([$role]);
        }
    }
}
