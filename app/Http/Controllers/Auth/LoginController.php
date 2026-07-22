<?php

namespace App\Http\Controllers\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserLoggedOut;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    use AuthenticatesUsers, RedirectsUsers;

    protected $redirectTo = '/login';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        if ($this->guard()->check()) {
            return redirect()->route($this->guard()->user()->redirect());
        }

        seo()->setTitle('Ingresar')->noindex(true);

        return view('auth.login');
    }

    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);

        return $this->guard()->attempt($credentials, $request->boolean('remember'));
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    protected function credentials(Request $request)
    {
        $login = $request->input($this->username());

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'identification';

        return [
            $field => $login,
            'password' => $request->input('password'),
        ];
    }

    protected function sendLoginResponse(Request $request)
    {
        if (! $this->guard()->user()->available) {
            $this->guard()->logout();
            $request->session()->invalidate();

            seo()->setTitle('Cuenta deshabilitada')->noindex(true);

            return view('auth.disabled');
        }

        $request->session()->regenerate();

        $previousSession = $this->guard()->user()->session;

        if ($previousSession) {
            Session::getHandler()->destroy($previousSession);
        }

        $this->guard()->user()->session = Session::getId();
        $this->guard()->user()->save();

        event(new UserLoggedIn($request->user()));

        $this->clearLoginAttempts($request);
        $fallback = route($this->guard()->user()->redirect());

        return $this->authenticated($request, $this->guard()->user()) ?: redirect()->intended($fallback);

    }

    protected function sendFailedLoginResponse(Request $request)
    {
        // Mensaje genérico para no revelar si el email existe (user enumeration).
        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([
                $this->username() => 'Las credenciales no coinciden con nuestros registros.',
            ]);
    }

    public function logout(Request $request)
    {
        // Capturar el usuario ANTES de logout(): después, $request->user() ya es
        // null y el listener de UserLoggedOut recibiría null.
        $user = $request->user();

        $this->guard()->logout();

        event(new UserLoggedOut($user));

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');

    }

    public function username()
    {
        return 'email';
    }

    protected function guard()
    {
        return Auth::guard();
    }
}
