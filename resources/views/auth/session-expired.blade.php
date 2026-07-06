@extends('layouts.pages')

@section('title', ($reason ?? null) === 'device' ? 'Sesión en otro dispositivo' : 'Sesión expirada')

@push('css')
<style>
    .sc {
        --navy: #0d1b2a;
        --navy-soft: #16304a;
        --ink: #1b2a3a;
        --muted: #6a7888;
        --green: #008bcd;
        --green-deep: #006fa3;
        --green-soft: #e4f2fb;
        --line: #e7ecf1;
        --shadow-card: 0 14px 40px rgba(13,27,42,.10), 0 2px 6px rgba(13,27,42,.05);
        --ease: cubic-bezier(.22,.61,.36,1);
        background: #f3f6f9;
        padding: 64px 24px 90px;
        min-height: 56vh;
        display: flex;
        align-items: flex-start;
        justify-content: center;
    }
    .sc-card {
        max-width: 560px;
        width: 100%;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 22px;
        box-shadow: var(--shadow-card);
        padding: 48px 44px;
        text-align: center;
    }
    .sc-icon {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        background: linear-gradient(150deg, var(--navy), var(--navy-soft));
        color: var(--green);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 26px;
        position: relative;
    }
    .sc-icon::after {
        content: '';
        position: absolute;
        inset: -12px;
        border-radius: 50%;
        background: var(--navy);
        opacity: .1;
        z-index: -1;
    }
    .sc-icon svg {
        width: 38px;
        height: 38px;
    }
    .sc-card h2 {
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -.01em;
        line-height: 1.2;
        margin: 0;
        color: var(--ink);
    }
    .sc-card p {
        color: var(--muted);
        font-size: 15px;
        line-height: 1.7;
        margin: 13px auto 0;
        max-width: 400px;
    }
    .sc-actions {
        display: flex;
        flex-direction: column;
        gap: 11px;
        max-width: 360px;
        margin: 32px auto 0;
    }
    .sc-actions a {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 15px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        transition: background .18s ease, transform .18s ease, border-color .18s ease, color .18s ease;
    }
    .sc-actions .primary {
        background: var(--navy);
        color: #fff;
        box-shadow: 0 10px 24px rgba(13,27,42,.18);
    }
    .sc-actions .primary:hover {
        background: var(--navy-soft);
        transform: translateY(-2px);
    }
    .sc-actions .primary svg {
        width: 16px;
        height: 16px;
        color: var(--green);
    }
    .sc-actions .ghost {
        background: #fff;
        color: var(--navy);
        border: 1.5px solid var(--line);
    }
    .sc-actions .ghost:hover {
        border-color: var(--green);
        color: var(--green-deep);
        background: var(--green-soft);
    }
</style>
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
