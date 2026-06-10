@extends('layouts.pages')

@section('title', 'Verificado')

@section('content')

    <section class="contact-area pb-100 pt-100">
        <div class="container">
            <div class="section-title">
                <span class="sub-title">¡BIENVENIDO A LA COMUNIDAD DE BPM!</span>
                <h2>Tu correo ha sido verificado</h2>
                <form class="form">
                    <p class="mb-4">¿No recibiste el mensaje?</p>
                    <a href="{{ route('home') }}" class="auth-btn pb-10">
                        <span class="label">CONTINUAR</span>
                    </a>
                </form>
            </div>
        </div>
    </section>

@endsection


