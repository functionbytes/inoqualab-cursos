@extends('layouts.pages')

@section('title', 'Página no encontrada')

@section('content')
<div class="content-error-area error-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-10 text-center">
                <div class="error-number error-hero-number">404</div>
                <h2 class="error-hero-title">Página no encontrada</h2>
                <p class="error-hero-text">
                    La página que buscas no existe o fue movida.<br>Revisa la URL o vuelve al inicio.
                </p>
                <div class="d-flex justify-content-center error-hero-actions">
                    <a href="{{ route('index') }}" class="theme-btn">
                        <i class="fas fa-house"></i> Volver al inicio
                    </a>
                    <a href="{{ route('courses') }}" class="theme-btn style-three">
                        <i class="fas fa-graduation-cap"></i> Ver cursos
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
