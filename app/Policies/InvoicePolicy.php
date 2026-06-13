<?php

namespace App\Policies;

use App\Models\Invoice\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Invoice pertenece a un distribuidor (distributor_id), no a un usuario
 * final. La autorización es por permiso; el ámbito por distribuidor se
 * resuelve en la capa de consulta del dominio Accountings/Distributors.
 */
class InvoicePolicy
{
    use HandlesAuthorization;

    private const ALIAS = 'invoices';

    public function viewAny(User $user): bool
    {
        return $user->can(self::ALIAS.'.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can(self::ALIAS.'.view');
    }

    public function create(User $user): bool
    {
        return $user->can(self::ALIAS.'.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can(self::ALIAS.'.update');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->can(self::ALIAS.'.delete');
    }

    public function manage(User $user): bool
    {
        return $user->can(self::ALIAS.'.manage');
    }
}
