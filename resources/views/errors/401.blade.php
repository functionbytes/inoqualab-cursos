@extends('layouts.pages')

@section('title', 'Acceso no autorizado')

@section('content')

<div class="content-error-area error-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-10 text-center">
                <div class="error-number error-hero-number"><i class="fas fa-lock"></i></div>
                <h2 class="error-hero-title">Necesitas iniciar sesión</h2>
                <p class="error-hero-text">
                    No tienes acceso a esta página. Inicia sesión con tu cuenta para continuar.
                </p>
                <div class="d-flex justify-content-center error-hero-actions">
                    <a href="{{ route('login') }}" class="theme-btn">
                        <i class="fas fa-right-to-bracket"></i> Iniciar sesión
                    </a>
                    <a href="{{ route('index') }}" class="theme-btn style-three">
                        <i class="fas fa-house"></i> Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('pages/css/errors/error-hero.css') }}">
@endpush
