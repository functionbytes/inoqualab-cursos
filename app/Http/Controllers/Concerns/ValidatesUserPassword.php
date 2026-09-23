<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Valida la política de contraseña cuando se resetea desde un panel admin.
 *
 * La regla de creación de usuarios (StoreUserRequest) exige Password::defaults(),
 * pero varios flujos de edición/reseteo de password de otros controladores solo
 * validaban la longitud client-side (jQuery Validate, bypasseable) -- una
 * petición directa al endpoint podia dejar una contraseña de 1 caracter.
 */
trait ValidatesUserPassword
{
    /**
     * Devuelve un mensaje de error si el password no cumple la política, o
     * null si esta vacío (nada que cambiar) o es válido.
     */
    protected function passwordValidationError(?string $password): ?string
    {
        if (empty($password)) {
            return null;
        }

        $validator = Validator::make(['password' => $password], [
            'password' => ['string', Password::defaults()],
        ]);

        return $validator->fails() ? $validator->errors()->first('password') : null;
    }
}
