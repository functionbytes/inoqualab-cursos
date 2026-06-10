<?php

namespace App\Http\Controllers\Supports\Concerns;

use App\Models\User;

/**
 * Restringe qué usuarios puede gestionar un agente de soporte.
 *
 * Un agente 'support' NO debe poder crear, editar ni eliminar usuarios con
 * roles privilegiados ('manager', 'support'); de lo contrario podría
 * auto-promoverse o promover a un cómplice a administrador (escalada de
 * privilegios). Solo puede operar sobre los roles operativos del negocio.
 */
trait RestrictsManageableUsers
{
    /**
     * Roles que un agente de soporte tiene permitido gestionar.
     *
     * @var array<int, string>
     */
    protected array $manageableRoles = ['customer', 'enterprise', 'distributor', 'accounting'];

    /**
     * Devuelve el usuario objetivo o aborta si no existe / no es gestionable.
     */
    protected function guardManageableUser(?User $user): User
    {
        abort_if($user === null, 404, 'Usuario no encontrado.');

        abort_unless(
            in_array($user->role, $this->manageableRoles, true),
            403,
            'No tienes autorización para gestionar este usuario.'
        );

        return $user;
    }

    /**
     * Aborta si el rol solicitado no está dentro de los gestionables.
     */
    protected function assertManageableRole(?string $role): void
    {
        abort_unless(
            in_array($role, $this->manageableRoles, true),
            403,
            'El rol seleccionado no está permitido.'
        );
    }

    /**
     * Regla de validación `in:` con los roles gestionables (para Form Requests
     * o Validator inline).
     */
    protected function manageableRolesRule(): string
    {
        return 'in:'.implode(',', $this->manageableRoles);
    }
}
