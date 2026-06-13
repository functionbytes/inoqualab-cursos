<?php

namespace App\Policies;

use App\Models\Course\Course;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Course es una entidad de catálogo (sin dueño individual). La
 * autorización es puramente por permiso.
 */
class CoursePolicy
{
    use HandlesAuthorization;

    private const ALIAS = 'courses';

    public function viewAny(User $user): bool
    {
        return $user->can(self::ALIAS.'.view');
    }

    public function view(User $user, Course $course): bool
    {
        return $user->can(self::ALIAS.'.view');
    }

    public function create(User $user): bool
    {
        return $user->can(self::ALIAS.'.create');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->can(self::ALIAS.'.update');
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->can(self::ALIAS.'.delete');
    }

    public function manage(User $user): bool
    {
        return $user->can(self::ALIAS.'.manage');
    }
}
