<?php

namespace App\Http\Controllers\Auth;

use App\Events\Auth\Password\ResetPasswordCreated;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
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

        return view('auth.passwords.reset')->with([
            'slack' => $slack,
            'email' => $user->email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'slack' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user = User::where('slack', $request->slack)->first();

        if ($user === null || ! $user->password_reset_token) {
            return redirect()->route('password.request')->withErrors([
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

        return view('auth.passwords.confirm')->with([
            'email' => $user->email,
        ]);
    }

    protected function guard()
    {
        return Auth::guard();
    }
}
