@extends('layouts.pages')

@section('title', 'Sitio en mantenimiento')

@section('content')

<div class="content-error-area error-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-10 text-center">
                <div class="error-number error-hero-number">503</div>
                <h2 class="error-hero-title">Sitio en mantenimiento</h2>
                <p class="error-hero-text">
                    Estamos haciendo mejoras en el sitio. Vuelve a intentarlo en unos minutos.
                </p>
                <div class="d-flex justify-content-center error-hero-actions">
                    <a href="{{ url()->current() }}" class="theme-btn">
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
