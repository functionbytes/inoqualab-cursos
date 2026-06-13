<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Users\Certificate;
use App\Policies\Concerns\ChecksOwnership;
use Illuminate\Auth\Access\HandlesAuthorization;

class CertificatePolicy
{
    use ChecksOwnership, HandlesAuthorization;

    private const ALIAS = 'certificates';

    public function viewAny(User $user): bool
    {
        return $user->can(self::ALIAS.'.view');
    }

    public function view(User $user, Certificate $certificate): bool
    {
        return $user->can(self::ALIAS.'.view')
            && $this->ownsOrManages($user, self::ALIAS, $certificate);
    }

    public function create(User $user): bool
    {
        return $user->can(self::ALIAS.'.create');
    }

    public function update(User $user, Certificate $certificate): bool
    {
        return $user->can(self::ALIAS.'.update')
            && $this->ownsOrManages($user, self::ALIAS, $certificate);
    }

    public function delete(User $user, Certificate $certificate): bool
    {
        return $user->can(self::ALIAS.'.delete')
            && $this->ownsOrManages($user, self::ALIAS, $certificate);
    }

    public function manage(User $user): bool
    {
        return $user->can(self::ALIAS.'.manage');
    }
}
