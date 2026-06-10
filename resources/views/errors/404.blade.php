@extends('layouts.pages')

@section('title', 'Página no encontrada')

@section('content')
<div class="content-error-area" style="padding: 100px 0 120px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-10 text-center">
                <div class="error-number" style="font-size: 130px; font-weight: 800; color: #008bce; line-height: 1; margin-bottom: 8px;">404</div>
                <h2 style="font-size: 28px; font-weight: 700; color: #081A28; margin-bottom: 16px;">Página no encontrada</h2>
                <p style="color: #5A7093; font-size: 16px; margin-bottom: 36px; max-width: 440px; margin-left: auto; margin-right: auto;">
                    La página que buscas no existe o fue movida.<br>Revisa la URL o vuelve al inicio.
                </p>
                <div class="d-flex justify-content-center" style="gap: 16px; flex-wrap: wrap;">
                    <a href="{{ route('index') }}" class="theme-btn">
                        <i class="fas fa-house" style="margin-right: 8px;"></i> Volver al inicio
                    </a>
                    <a href="{{ route('courses') }}" class="theme-btn style-three">
                        <i class="fas fa-graduation-cap" style="margin-right: 8px;"></i> Ver cursos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
