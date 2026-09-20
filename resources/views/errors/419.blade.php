@extends('layouts.pages')

@section('title', 'Página expirada')

@section('content')

<div class="content-error-area error-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-10 text-center">
                <div class="error-number error-hero-number"><i class="fas fa-hourglass"></i></div>
                <h2 class="error-hero-title">La página expiró</h2>
                <p class="error-hero-text">
                    Tu sesión o el formulario expiraron por inactividad. Actualiza la página e intenta de nuevo.
                </p>
                <div class="d-flex justify-content-center error-hero-actions">
                    <a href="{{ url()->previous() ?: route('index') }}" class="theme-btn">
                        <i class="fas fa-rotate-right"></i> Reintentar
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
