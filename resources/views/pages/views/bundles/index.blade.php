@extends('layouts.pages')

@section('title', 'Paquetes de cursos · INOQUALAB')
@section('active', 'bundles')

@push('css')
<link rel="stylesheet" href="{{ url('/pages/css/storefront.css') }}?v={{ @filemtime(public_path('pages/css/storefront.css')) ?: '1' }}">
<link rel="stylesheet" href="{{ url('/pages/css/cursos.css') }}?v={{ @filemtime(public_path('pages/css/cursos.css')) ?: '1' }}">
<link rel="stylesheet" href="{{ asset('pages/css/views/bundles/index.css') }}">
@endpush

@section('content')

    {{-- ===== Band ===== --}}
    <div class="band">
        <div class="container">
            <div class="crumb">
                <a href="{{ route('index') }}">INICIO</a>
                <span class="sep">/</span>
                <span class="cur">PAQUETES</span>
            </div>
            <h1>Paquetes de cursos</h1>
            <p class="lede">Adquiere varios cursos agrupados a un mejor precio y certifícate en toda un área.</p>
        </div>
    </div>

    {{-- ===== Listado de paquetes =====
         Misma tarjeta que la franja del inicio (pages.partials.components.bundle-card);
         sus estilos viven en cursos.css bajo .cursos-page .bundles-strip. --}}
    <main class="pkglist cursos-page">
        <div class="container">

            @if ($bundles->isEmpty())
                <p class="pkglist-count">Aún no hay paquetes disponibles.</p>
            @else
                <p class="pkglist-count">
                    Mostrando <b>{{ $bundles->count() }}</b> {{ $bundles->count() == 1 ? 'paquete' : 'paquetes' }}
                </p>

                <div class="bundles-strip bundles-strip--page">
                    <div class="row">
                        @foreach ($bundles as $bundle)
                            <div class="col-lg-4 col-md-6">
                                @include('pages.partials.components.bundle-card', ['bundle' => $bundle])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </main>

@endsection
