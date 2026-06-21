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
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $user = User::where('email', $request->email)
            ->orWhere('identification', $request->email)
            ->first();

        if ($user === null) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'El correo o cedula no coincide con nuestros registros.',
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

        return view('auth.passwords.success')->with([
            'email' => $user->email,
        ]);
    }

    public function username(): string
    {
        return 'email';
    }
}
