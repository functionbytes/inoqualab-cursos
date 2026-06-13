<?php

namespace App\Policies;

use App\Models\Inscription;
use App\Models\User;
use App\Policies\Concerns\ChecksOwnership;
use Illuminate\Auth\Access\HandlesAuthorization;

class InscriptionPolicy
{
    use ChecksOwnership, HandlesAuthorization;

    private const ALIAS = 'inscriptions';

    public function viewAny(User $user): bool
    {
        return $user->can(self::ALIAS.'.view');
    }

    public function view(User $user, Inscription $inscription): bool
    {
        return $user->can(self::ALIAS.'.view')
            && $this->ownsOrManages($user, self::ALIAS, $inscription);
    }

    public function create(User $user): bool
    {
        return $user->can(self::ALIAS.'.create');
    }

    public function update(User $user, Inscription $inscription): bool
    {
        return $user->can(self::ALIAS.'.update')
            && $this->ownsOrManages($user, self::ALIAS, $inscription);
    }

    public function delete(User $user, Inscription $inscription): bool
    {
        return $user->can(self::ALIAS.'.delete')
            && $this->ownsOrManages($user, self::ALIAS, $inscription);
    }

    public function manage(User $user): bool
    {
        return $user->can(self::ALIAS.'.manage');
    }
}
