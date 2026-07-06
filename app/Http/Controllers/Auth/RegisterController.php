<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreRegisterRequest;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegisterForm()
    {
        seo()->setTitle('Registro')->noindex(true);

        return view('auth.register');
    }

    public function register(StoreRegisterRequest $request)
    {
        if (setting('registration_enabled') === 0 || setting('registration_enabled') === '0') {
            return redirect()->route('login')
                ->with('error', 'El registro de nuevas cuentas está deshabilitado en este momento.');
        }

        $user = User::create([
            'slack' => Str::uuid(),
            'email' => $request->email,
            'password' => $request->password,
            'available' => 1,
            'verified' => 0,
            'validation' => 0,
            'terms' => 1,
        ]);

        // Asignación directa (no mass assignment): el registro público siempre
        // crea clientes, sin importar lo que traiga el request.
        $user->role = 'customer';
        $user->save();

        event(new Registered($user));

        Newsletter::query()->firstOrCreate(
            ['email' => $user->email],
            ['user_id' => $user->id, 'source' => 'registration', 'slack' => Str::uuid()]
        );

        return redirect()->route('login')
            ->with('success', 'Cuenta creada. Por favor verifica tu correo electrónico.');
    }
}
