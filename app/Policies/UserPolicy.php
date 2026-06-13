<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Un usuario siempre puede ver/editar su propio perfil; gestionar a otros
 * requiere el permiso `users.*`. `users.manage` bypassa la propiedad.
 */
class UserPolicy
{
    use HandlesAuthorization;

    private const ALIAS = 'users';

    public function viewAny(User $user): bool
    {
        return $user->can(self::ALIAS.'.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->can(self::ALIAS.'.view');
    }

    public function create(User $user): bool
    {
        return $user->can(self::ALIAS.'.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->can(self::ALIAS.'.update');
    }

    public function delete(User $user, User $model): bool
    {
        // Nadie se elimina a sí mismo; requiere permiso explícito.
        return $user->id !== $model->id && $user->can(self::ALIAS.'.delete');
    }

    public function manage(User $user): bool
    {
        return $user->can(self::ALIAS.'.manage');
    }
}
