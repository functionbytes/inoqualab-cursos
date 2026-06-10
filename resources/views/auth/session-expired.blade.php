@extends('layouts.pages')

@section('title', 'Sesión expirada')

@section('content')

<section class="account pt-150 padding-bottom">
    <div class="container-fluid">
        <div class="account__wrapper" data-aos="fade-up" data-aos-duration="800">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-6 col-md-8 col-sm-12">
                    <div class="account__content text-center">

                        <div class="account__header pb-30">
                            <div class="mb-4">
                                @if (($reason ?? null) === 'device')
                                    <i class="fa-duotone fa-laptop-mobile" style="font-size: 3.5rem; color: #081A28; opacity: 0.7;"></i>
                                @else
                                    <i class="fa-duotone fa-lock-keyhole" style="font-size: 3.5rem; color: #081A28; opacity: 0.7;"></i>
                                @endif
                            </div>
                            @if (($reason ?? null) === 'device')
                                <h3 class="pb-5">Sesión iniciada en otro dispositivo</h3>
                                <p>Tu cuenta se conectó desde otro dispositivo o navegador. Por seguridad, esta sesión se cerró. Si no fuiste tú, cambia tu contraseña al ingresar.</p>
                            @else
                                <h3 class="pb-5">Sesión cerrada</h3>
                                <p>Tu sesión ha expirado por inactividad. Por favor vuelve a ingresar para continuar donde lo dejaste.</p>
                            @endif
                        </div>

                        <div class="d-flex flex-column gap-3 mt-4">
                            <a href="{{ route('login') }}" class="trk-btn trk-btn--border trk-btn--secondary1 d-block">
                                Iniciar sesión nuevamente
                            </a>
                            <a href="{{ route('index') }}" class="trk-btn d-block" style="background:transparent; border:2px solid #081A28; color:#081A28;">
                                Ir al inicio
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
