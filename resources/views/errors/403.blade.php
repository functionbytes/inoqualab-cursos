@extends('layouts.pages')

@section('title', 'Acceso denegado')

@push('css')
<link rel="stylesheet" href="{{ asset('pages/css/errors/403.css') }}">
@endpush

@section('content')

<main class="sc">
    <div class="sc-card">

        <div class="sc-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <rect x="5" y="11" width="14" height="9" rx="2"/>
                <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                <circle cx="12" cy="15.5" r="1.3" fill="currentColor" stroke="none"/>
            </svg>
        </div>

        <h2>No tienes permiso para acceder aquí</h2>
        <p>Tu rol no incluye el permiso necesario para esta sección. Si crees que es un error, contacta a un administrador.</p>

        <div class="sc-actions">
            <a class="primary" href="{{ url()->previous() }}">
                Volver atrás
            </a>
            <a class="ghost" href="{{ route('index') }}">
                Ir al inicio
            </a>
        </div>

    </div>
</main>

@endsection
