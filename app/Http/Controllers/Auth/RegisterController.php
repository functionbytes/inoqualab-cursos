<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (setting('registration_enabled') === 0 || setting('registration_enabled') === '0') {
            return redirect()->route('login')
                ->with('error', 'El registro de nuevas cuentas está deshabilitado en este momento.');
        }

        $request->validate([
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'slack' => Str::uuid(),
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'customer',
            'available' => 1,
            'verified' => 0,
            'validation' => 0,
            'terms' => 1,
        ]);

        event(new Registered($user));

        return redirect()->route('login')
            ->with('success', 'Cuenta creada. Por favor verifica tu correo electrónico.');
    }
}
