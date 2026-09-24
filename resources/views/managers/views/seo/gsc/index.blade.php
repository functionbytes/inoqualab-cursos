@extends('layouts.managers')

@section('title', 'Google Search Console')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Google Search Console'])
@endsection

@section('content')


    <div class="widget-content"
         data-flash-success="{{ session('success') }}" data-flash-success-title="Éxito"
         data-flash-error="{{ session('error') }}" data-flash-error-title="Error">

        {{-- ── Conexión: 3 pasos en orden (credenciales → autorizar → importar) ── --}}
        @php
            $currentStep = ! $status['configured'] ? 1 : (! $status['connected'] ? 2 : 3);
            $statusBadge = match ($currentStep) {
                1 => ['label' => 'Sin credenciales', 'class' => 'bg-secondary-subtle text-secondary'],
                2 => ['label' => 'Pendiente de autorizar', 'class' => 'bg-secondary-subtle text-secondary'],
                default => ['label' => 'Conectado', 'class' => 'bg-success-subtle text-success'],
            };
            $steps = [
                1 => $status['configured'],
                2 => $status['connected'],
                3 => $status['imported'],
            ];
        @endphp

        <div class="card mb-3">
            <div class="card-header border-bottom">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <h6 class="mb-1 fw-bold">Conexión con Google Search Console</h6>
                        <p class="text-muted small mb-0">
                            Trae clicks, impresiones, CTR y posición media de cada página desde Google.
                            Completa los pasos en orden; cada uno habilita el siguiente.
                        </p>
                    </div>
                    <span class="badge {{ $statusBadge['class'] }} flex-shrink-0">{{ $statusBadge['label'] }}</span>
                </div>
            </div>

            <div class="card-body">
                <ol class="gsc-steps">

                    {{-- Paso 1: credenciales --}}
                    <li class="gsc-step {{ $steps[1] ? 'is-done' : ($currentStep === 1 ? 'is-current' : '') }}">
                        <span class="gsc-step-marker">
                            @if($steps[1]) {!! \App\Html\IconHelper::render('check', 14) !!} @else 1 @endif
                        </span>
                        <div class="gsc-step-body">
                            <h6 class="fw-bold mb-1">Credenciales de Google Cloud</h6>
                            @if($steps[1])
                                <p class="text-muted small mb-0">Client ID, Client Secret y URL de la propiedad están en el <code>.env</code>.</p>
                            @else
                                <p class="text-muted small mb-2">
                                    Crea un proyecto en Google Cloud Console con la API de Search Console habilitada
                                    y agrega estas variables al <code>.env</code> del servidor:
                                </p>
                                <pre class="gsc-env mb-0">GSC_CLIENT_ID=tu_client_id_aqui
GSC_CLIENT_SECRET=tu_client_secret_aqui
GSC_PROPERTY_URL=https://tusitio.com/</pre>
                            @endif
                        </div>
                    </li>

                    {{-- Paso 2: autorizar la cuenta --}}
                    <li class="gsc-step {{ $steps[2] ? 'is-done' : ($currentStep === 2 ? 'is-current' : 'is-locked') }}">
                        <span class="gsc-step-marker">
                            @if($steps[2]) {!! \App\Html\IconHelper::render('check', 14) !!} @else 2 @endif
                        </span>
                        <div class="gsc-step-body">
                            <div class="gsc-step-row">
                                <div>
                                    <h6 class="fw-bold mb-1">Autorizar la cuenta de Google</h6>
                                    @if($steps[2])
                                        <p class="text-muted small mb-0">
                                            Conectado a <span class="font-monospace">{{ $status['property_url'] }}</span>.
                                            Puedes revocar el acceso cuando quieras.
                                        </p>
                                    @else
                                        <p class="text-muted small mb-0">
                                            Te lleva a Google para aprobar el acceso de solo lectura a la propiedad.
                                            Al volver, la conexión queda guardada.
                                        </p>
                                    @endif
                                </div>
                                @if($steps[2])
                                    <form method="POST" action="{{ route('manager.seo.gsc.disconnect') }}" id="form-disconnect" class="flex-shrink-0">
                                        @csrf
                                        <button type="button" class="btn btn-outline-secondary js-btn-disconnect">Desconectar</button>
                                    </form>
                                @elseif($currentStep === 2)
                                    <a href="{{ route('manager.seo.gsc.connect') }}" class="btn btn-primary flex-shrink-0">Conectar con Google</a>
                                @endif
                            </div>
                        </div>
                    </li>

                    {{-- Paso 3: importar datos --}}
                    <li class="gsc-step {{ $steps[3] ? 'is-done' : ($currentStep === 3 ? 'is-current' : 'is-locked') }}">
                        <span class="gsc-step-marker">
                            @if($steps[3]) {!! \App\Html\IconHelper::render('check', 14) !!} @else 3 @endif
                        </span>
                        <div class="gsc-step-body">
                            <h6 class="fw-bold mb-1">Importar datos de rendimiento</h6>
                            <p class="text-muted small {{ $status['connected'] ? 'mb-3' : 'mb-0' }}">
                                @if($steps[3] && $status['connected'])
                                    Ya hay métricas importadas en las páginas. Vuelve a importar para actualizarlas.
                                @elseif($steps[3])
                                    Hay métricas de una importación anterior. Conecta la cuenta para actualizarlas.
                                @else
                                    Guarda las métricas de Google en cada página del sitio para verlas en el Dashboard SEO.
                                @endif
                            </p>
                            @if($status['connected'])
                                <form method="GET" action="{{ route('manager.seo.gsc.import') }}" class="gsc-import-form">
                                    <select name="days" id="days" class="form-select" aria-label="Período a importar">
                                        <option value="7">Últimos 7 días</option>
                                        <option value="14">Últimos 14 días</option>
                                        <option value="28" selected>Últimos 28 días</option>
                                        <option value="60">Últimos 60 días</option>
                                        <option value="90">Últimos 90 días</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary">Importar datos</button>
                                </form>
                            @endif
                        </div>
                    </li>

                </ol>
            </div>
        </div>

        {{-- ── Qué significa cada métrica ──────────────────────────────────── --}}
        <div class="card">
            <div class="card-header border-bottom">
                <h6 class="mb-1 fw-bold">Qué datos se importan</h6>
                <p class="text-muted small mb-0">
                    Las cuatro métricas que Google Search Console registra por página. El acceso se puede revocar en
                    <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener">los permisos de tu cuenta de Google</a>.
                </p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-xl-3">
                        <div class="gsc-metric">
                            <h6 class="fw-bold mb-1">Clicks</h6>
                            <p class="text-muted small mb-0">Veces que alguien hizo clic en tu sitio desde los resultados de Google.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="gsc-metric">
                            <h6 class="fw-bold mb-1">Impresiones</h6>
                            <p class="text-muted small mb-0">Veces que una página apareció en los resultados, aunque nadie hiciera clic.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="gsc-metric">
                            <h6 class="fw-bold mb-1">CTR</h6>
                            <p class="text-muted small mb-0">Porcentaje de impresiones que terminaron en clic. Un CTR bajo sugiere mejorar el título o la descripción.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="gsc-metric">
                            <h6 class="fw-bold mb-1">Posición media</h6>
                            <p class="text-muted small mb-0">Lugar promedio en los resultados. Cuanto más bajo el número, más arriba apareces.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/gsc/index.css') }}?v={{ @filemtime(public_path('managers/css/views/seo/gsc/index.css')) ?: 1 }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/seo/gsc/index.js') }}"></script>
@endpush
