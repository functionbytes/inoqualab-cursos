<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckSession;
use App\Http\Middleware\HandleSeoRedirects;
use App\Http\Middleware\IsAccountings;
use App\Http\Middleware\IsCustomer;
use App\Http\Middleware\IsDistributor;
use App\Http\Middleware\IsEnterprise;
use App\Http\Middleware\IsManager;
use App\Http\Middleware\IsProfile;
use App\Http\Middleware\IsSupport;
use App\Http\Middleware\IsUpgrade;
use App\Http\Middleware\IsVerified;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrackSeo404;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/accountings.php'));
            Route::middleware('web')->group(base_path('routes/managers.php'));
            Route::middleware('web')->group(base_path('routes/enterprises.php'));
            Route::middleware('web')->group(base_path('routes/customers.php'));
            Route::middleware('web')->group(base_path('routes/distributors.php'));
            Route::middleware('web')->group(base_path('routes/supports.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(prepend: [
            HandleSeoRedirects::class,
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
            TrackSeo404::class,
        ]);

        $middleware->trustProxies(
            at: '127.0.0.1',
            headers: Request::HEADER_X_FORWARDED_FOR |
                     Request::HEADER_X_FORWARDED_HOST |
                     Request::HEADER_X_FORWARDED_PORT |
                     Request::HEADER_X_FORWARDED_PROTO |
                     Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.basic' => AuthenticateWithBasicAuth::class,
            'auth.session' => AuthenticateSession::class,
            'guest' => RedirectIfAuthenticated::class,
            'verified' => IsVerified::class,
            'manager' => IsManager::class,
            'support' => IsSupport::class,
            'enterprise' => IsEnterprise::class,
            'upgrade' => IsUpgrade::class,
            'customers' => IsCustomer::class,
            'profile' => IsProfile::class,
            'distributor' => IsDistributor::class,
            'accounting' => IsAccountings::class,
            'session' => CheckSession::class,
            'signed' => ValidateSignature::class,
            // Spatie Permission: autorización granular por permiso/rol en rutas.
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Exception $e, $request) {
            if ($e->getMessage() === 'processing data') {
                return redirect()->route('admin.testinginfo');
            }
            if ($e->getMessage() === 'error response') {
                return new Response('');
            }
            if ($e->getMessage() === 'importingerror') {
                return redirect()->back()->with('error', 'You are selected different file, please select customers import file.');
            }
        });

        // CSRF expirado (sesión caducada durante un POST) → redirigir a session-expired
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesión expirada.'], 419);
            }
            // Para POST guardamos el referer (la página GET que originó el form)
            if ($referer = $request->header('referer')) {
                $request->session()->put('url.intended', $referer);
            }

            return redirect()->route('session.expired');
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            if ($request->is('customers') || $request->is('customers/*')) {
                return redirect()->guest('/customers/login');
            }
            if ($request->is('admin') || $request->is('admin/*')) {
                return redirect()->guest('/admin/login');
            }

            return redirect()->guest(route('login'));
        });
    })->create();
