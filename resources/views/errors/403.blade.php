@extends('layouts.pages')

@section('title', 'Acceso denegado')

@section('content')


<div class="content-error-area pt-120 pb-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="content-error-item text-center">
                    <div class="error-thumb">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div class="section-title">
                        <h2 class="mb-20">No tienes permiso para acceder aquí</h2>
                        <p>Tu rol no incluye el permiso necesario para esta sección. Si crees que es un error, contacta a un administrador.</p>
                    </div>
                    <div class="error-btn">
                        <a class="edu-btn" href="{{ url()->previous() }}">Volver atrás</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection