<?php

namespace App\Policies;

use App\Models\Order\Order;
use App\Models\User;
use App\Policies\Concerns\ChecksOwnership;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use ChecksOwnership, HandlesAuthorization;

    private const ALIAS = 'orders';

    public function viewAny(User $user): bool
    {
        return $user->can(self::ALIAS.'.view');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->can(self::ALIAS.'.view')
            && $this->ownsOrManages($user, self::ALIAS, $order);
    }

    public function create(User $user): bool
    {
        return $user->can(self::ALIAS.'.create');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can(self::ALIAS.'.update')
            && $this->ownsOrManages($user, self::ALIAS, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can(self::ALIAS.'.delete')
            && $this->ownsOrManages($user, self::ALIAS, $order);
    }

    public function manage(User $user): bool
    {
        return $user->can(self::ALIAS.'.manage');
    }
}
