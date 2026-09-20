@extends('layouts.pages')

@section('title', 'Pago requerido')

@section('content')

<div class="content-error-area error-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-10 text-center">
                <div class="error-number error-hero-number"><i class="fas fa-credit-card"></i></div>
                <h2 class="error-hero-title">Se requiere un pago</h2>
                <p class="error-hero-text">
                    Esta acción necesita que completes un pago pendiente antes de continuar.
                </p>
                <div class="d-flex justify-content-center error-hero-actions">
                    <a href="{{ route('index') }}" class="theme-btn">
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
