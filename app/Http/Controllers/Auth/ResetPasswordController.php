<?php

namespace App\Http\Controllers\Auth;

use App\Events\Auth\Password\ResetPasswordCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showResetForm($slack)
    {
        $user = User::where('slack', $slack)->firstOrFail();

        seo()->setTitle('Restablecer contraseña')->noindex(true);

        // El token viaja al form (hidden) para atarlo al POST: la ruta GET está
        // protegida con `signed`, pero el POST no, así que sin verificar el token
        // ahí, conocer el slack + disparar un reset bastaba para tomar la cuenta.
        return view('auth.passwords.reset')->with([
            'slack' => $slack,
            'email' => $user->email,
            'token' => $user->password_reset_token,
        ]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $user = User::where('slack', $request->slack)->first();

        // Token ausente/no coincidente, o enlace de más de 24h: rechazar. hash_equals
        // evita timing attacks; la ventana de 24h coincide con la del enlace firmado.
        $tokenOk = $user !== null
            && $user->password_reset_token
            && hash_equals((string) $user->password_reset_token, (string) $request->token);

        $notExpired = $user !== null
            && $user->password_reset_last_tried_on
            && Carbon::parse($user->password_reset_last_tried_on)->gt(Carbon::now()->subHours(24));

        if (! $tokenOk || ! $notExpired) {
            return redirect()->route('password.confirm')->withErrors([
                'email' => 'El enlace de recuperación ha expirado o ya fue utilizado.',
            ]);
        }

        $user->password = $request->password;
        $user->remember_token = Str::random(60);
        $user->password_reset_token = null;
        $user->password_reset_max_tries = null;
        $user->password_reset_last_tried_on = null;

        // Expulsa la sesión que estuviera abierta. Es el punto del flujo
        // "olvidé mi contraseña": si se restablece porque alguien entró en la
        // cuenta, ese alguien tiene que quedarse fuera aunque la víctima no
        // vuelva a iniciar sesión inmediatamente.
        revokeUserSessions($user);

        $user->save();

        event(new ResetPasswordCreated($user));

        seo()->setTitle('Contraseña actualizada')->noindex(true);

        return view('auth.passwords.confirm')->with([
            'email' => $user->email,
        ]);
    }

    protected function guard()
    {
        return Auth::guard();
    }
}
