<?php

namespace App\Http\Controllers\Auth;

use App\Events\Auth\Password\ResetPasswordCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
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

        return view('auth.passwords.reset')->with([
            'slack' => $slack,
            'email' => $user->email,
        ]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $user = User::where('slack', $request->slack)->first();

        if ($user === null || ! $user->password_reset_token) {
            return redirect()->route('password.confirm')->withErrors([
                'email' => 'El enlace de recuperación ha expirado o ya fue utilizado.',
            ]);
        }

        $user->password = $request->password;
        $user->remember_token = Str::random(60);
        $user->password_reset_token = null;
        $user->password_reset_max_tries = null;
        $user->password_reset_last_tried_on = null;
        $user->save();

        $user->sessions()->delete();

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
