<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Helper compartido por las Policies de entidades con dueño (`user_id`).
 * El permiso `{alias}.manage` actúa como super-permiso que bypassa el
 * chequeo de propiedad (lo tienen manager y roles administrativos).
 */
trait ChecksOwnership
{
    protected function ownsOrManages(User $user, string $alias, mixed $entity): bool
    {
        if ($user->can("{$alias}.manage")) {
            return true;
        }

        return (int) ($entity->user_id ?? 0) === (int) $user->id;
    }
}
