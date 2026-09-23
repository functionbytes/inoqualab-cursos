@extends('layouts.pages')

@section('title', 'Verificación')

@push('css')
<link rel="stylesheet" href="{{ asset('auth/css/validation.css') }}">
@endpush

@section('content')

<main class="sc">
    <div class="sc-card">

        <div class="sc-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="5" y="11" width="14" height="10" rx="2"/>
                <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                <circle cx="12" cy="16" r="1.4" fill="currentColor" stroke="none"/>
            </svg>
        </div>

        <h2>No tienes privilegios</h2>
        <p>Para completar el proceso de registro es necesario que verifiques tu cuenta, para esto hemos enviado un mensaje a la dirección de correo en el que encontrarás un link que te traerá de vuelta a la plataforma.</p>

        <div class="sc-actions">
            <a class="primary" href="{{ route('home') }}">
                Regresar
            </a>
        </div>

    </div>
</main>

@endsection
