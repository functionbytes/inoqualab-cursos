@extends('layouts.pages')

@section('title', 'Verificación')

@section('content')


<div class="content-error-area pt-120 pb-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="content-error-item text-center">
                    <div class="error-thumb">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                    <div class="section-title">
                        <h2 class="mb-20">¡Ups! No se pudo encontrar esa página.</h2>
                        <p>No pudimos encontrar ningún resultado </p>
                    </div>
                    <div class="error-btn">
                        <a class="edu-btn" href="/">Volver a la página de inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection