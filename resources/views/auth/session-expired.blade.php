@extends('layouts.pages')

@section('title', ($reason ?? null) === 'device' ? 'Sesión en otro dispositivo' : 'Sesión expirada')

@push('css')
<link rel="stylesheet" href="{{ asset('auth/css/session-expired.css') }}">
@endpush

@section('content')

<main class="sc">
    <div class="sc-card">

        <div class="sc-icon">
            @if (($reason ?? null) === 'device')
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="5" width="14" height="10" rx="2"/>
                    <path d="M2 19h10"/>
                    <rect x="16" y="11" width="6" height="10" rx="1.5"/>
                </svg>
            @else
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="11" width="16" height="10" rx="2"/>
                    <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                </svg>
            @endif
        </div>

        @if (($reason ?? null) === 'device')
            <h2>Sesión iniciada en otro dispositivo</h2>
            <p>Tu cuenta se conectó desde otro dispositivo o navegador. Por seguridad, esta sesión se cerró. Si no fuiste tú, cambia tu contraseña al ingresar.</p>
        @else
            <h2>Sesión cerrada</h2>
            <p>Tu sesión ha expirado por inactividad. Por favor, vuelve a ingresar para continuar donde lo dejaste.</p>
        @endif

        <div class="sc-actions">
            <a class="primary" href="{{ route('login') }}">
                Iniciar sesión nuevamente
            </a>
            <a class="ghost" href="{{ route('index') }}">
                Ir al inicio
            </a>
        </div>

    </div>
</main>

@endsection
