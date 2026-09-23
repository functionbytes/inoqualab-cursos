<?php

namespace App\Http\Controllers\Auth;

use App\Events\Auth\Password\ForgotPasswordCreated;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showLinkRequest()
    {
        seo()->setTitle('Recuperar contraseña')->noindex(true);

        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        // Agrupado en closure: sin él, el orWhere() de identification queda a
        // nivel superior y por precedencia de operadores escapa del whereNull
        // deleted_at del scope de SoftDeletes -- un usuario soft-deleted que
        // matcheara por identification recibiría el link de reset igual.
        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->email)
                ->orWhere('identification', $request->email);
        })->first();

        // No revelar si el email/cédula existe (user enumeration) -- mismo
        // criterio ya aplicado en LoginController::sendFailedLoginResponse().
        // Se muestra la misma pantalla de éxito sin enviar nada realmente.
        if ($user === null) {
            seo()->setTitle('Correo enviado')->noindex(true);

            return view('auth.passwords.success')->with([
                'email' => $request->email,
            ]);
        }

        $currentDate = now()->toDateString();
        $lastTriedDate = $user->password_reset_last_tried_on
            ? date('Y-m-d', strtotime($user->password_reset_last_tried_on))
            : null;

        if ($lastTriedDate === $currentDate && $user->password_reset_max_tries >= 3) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'tried' => 'Ya lo has probado 3 veces hoy. Comuníquese con el administrador para restablecer la contraseña.',
                ]);
        }

        $resetTries = ($lastTriedDate === $currentDate)
            ? $user->password_reset_max_tries + 1
            : 1;

        $user->password_reset_token = Str::random(50);
        $user->password_reset_max_tries = $resetTries;
        $user->password_reset_last_tried_on = now();
        $user->save();

        event(new ForgotPasswordCreated($user));

        seo()->setTitle('Correo enviado')->noindex(true);

        return view('auth.passwords.success')->with([
            'email' => $user->email,
        ]);
    }

    public function username(): string
    {
        return 'email';
    }
}
