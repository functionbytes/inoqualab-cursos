<?php

namespace App\Http\Controllers\Supports\Concerns;

use App\Models\User;

/**
 * Valida unicidad de email/identificación de usuarios.
 *
 * Reemplaza el bloque if/else anidado que estaba duplicado en varios
 * controladores de soporte y que tenía un camino muerto: cuando el email y la
 * identificación cambiaban ambos a valores nuevos y únicos, el código original
 * no actualizaba nada ni devolvía respuesta.
 */
trait ValidatesUniqueUserFields
{
    /**
     * Devuelve un mensaje de error si el email o la identificación ya existen
     * en otro usuario, o null si ambos están libres.
     *
     * @param  User|null  $ignore  Usuario a excluir de la comprobación (en updates).
     */
    protected function uniqueUserFieldError(?string $email, ?string $identification, ?User $ignore = null): ?string
    {
        $ignoreId = $ignore?->id;

        if (! empty($email)) {
            $emailExists = User::query()
                ->where('email', $email)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists();

            if ($emailExists) {
                return 'El correo electrónico ya está registrado en nuestro sistema.';
            }
        }

        if (! empty($identification)) {
            $identificationExists = User::query()
                ->where('identification', $identification)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists();

            if ($identificationExists) {
                return 'La identificación ya está registrada en nuestro sistema.';
            }
        }

        return null;
    }
}
