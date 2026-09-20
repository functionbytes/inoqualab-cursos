@extends('layouts.managers')

@section('title', 'Google Search Console')

@section('content')


    <div class="widget-content">

        {{-- ── Tarjeta de estado ─────────────────────────────────────────────── --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0
                             {{ $status['configured'] ? 'bg-success-subtle' : 'bg-secondary-subtle' }} icon-box-44">
                            <i class="fas fa-key fs-5 {{ $status['configured'] ? 'text-success' : 'text-secondary' }}"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">API Keys</p>
                            <h6 class="fw-semibold mb-0">
                                @if($status['configured'])
                                    <span class="badge bg-success">Configurado</span>
                                @else
                                    <span class="badge bg-secondary">No configurado</span>
                                @endif
                            </h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0
                             {{ $status['connected'] ? 'bg-success-subtle' : 'bg-secondary-subtle' }} icon-box-44">
                            <i class="fas fa-plug fs-5 {{ $status['connected'] ? 'text-success' : 'text-secondary' }}"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Token OAuth</p>
                            <h6 class="fw-semibold mb-0">
                                @if($status['connected'])
                                    <span class="badge bg-success">Conectado</span>
                                @else
                                    <span class="badge bg-secondary">Sin conexion</span>
                                @endif
                            </h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0
                             {{ $status['property_url'] ? 'bg-primary-subtle' : 'bg-secondary-subtle' }} icon-box-44">
                            <i class="fas fa-globe fs-5 {{ $status['property_url'] ? 'text-primary' : 'text-secondary' }}"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-muted small mb-0">Propiedad GSC</p>
                            <h6 class="fw-semibold mb-0 text-truncate">
                                @if($status['property_url'])
                                    <small class="font-monospace text-primary">{{ $status['property_url'] }}</small>
                                @else
                                    <span class="badge bg-secondary">No definida</span>
                                @endif
                            </h6>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Estado: no configurado ────────────────────────────────────────── --}}
        @if(!$status['configured'])
            <div class="alert alert-info d-flex gap-2 mb-3" role="alert">
                <i class="fas fa-circle-info flex-shrink-0 mt-1"></i>
                <div>
                    <h6 class="fw-semibold mb-1">Configura las credenciales de Google</h6>
                    <p class="mb-2 small">
                        Para conectar con Google Search Console necesitas un proyecto en
                        <strong>Google Cloud Console</strong> con la API de Search Console habilitada.
                    </p>
                    <p class="mb-2 small">Agrega las siguientes variables en tu archivo <code>.env</code>:</p>
                    <pre class="bg-white border rounded p-2 small mb-0">GSC_CLIENT_ID=tu_client_id_aqui
GSC_CLIENT_SECRET=tu_client_secret_aqui
GSC_PROPERTY_URL=https://tusitio.com/</pre>
                </div>
            </div>
        @endif

        {{-- ── Estado: configurado pero no conectado ────────────────────────── --}}
        @if($status['configured'] && !$status['connected'])
            <div class="card mb-3">
                <div class="card-body text-center py-5">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-primary-subtle icon-box-64">
                        <i class="fab fa-google text-primary fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Conecta tu cuenta de Google</h5>
                    <p class="text-muted mb-4">
                        Autoriza el acceso a Google Search Console para importar datos de rendimiento.
                    </p>
                    <a href="{{ route('manager.seo.gsc.connect') }}" class="btn btn-primary px-4">
                        Conectar con Google
                    </a>
                </div>
            </div>
        @endif

        {{-- ── Estado: conectado ─────────────────────────────────────────────── --}}
        @if($status['connected'])
            <div class="row g-3 mb-3">

                {{-- Importar datos --}}
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header border-bottom p-3">
                            <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                                <span class="badge bg-success">Conectado</span>
                                Importar datos
                            </h5>
                            <p class="text-muted">Trae métricas de rendimiento desde GSC</p>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('manager.seo.gsc.import') }}">
                                <div class="mb-3">
                                    <label for="days" class="form-label fw-semibold">Período a importar</label>
                                    <select name="days" id="days" class="form-select">
                                        <option value="7">Últimos 7 días</option>
                                        <option value="14">Últimos 14 días</option>
                                        <option value="28" selected>Últimos 28 días</option>
                                        <option value="60">Últimos 60 días</option>
                                        <option value="90">Últimos 90 días</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    Importar datos GSC
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Desconectar --}}
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header border-bottom p-3">
                            <h5 class="mb-0 fw-bold">Gestion de conexion</h5>
                            <p class="text-muted">Administra el acceso OAuth de Google</p>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="mb-3">
                                @if($status['property_url'])
                                    <p class="text-muted small mb-1">Propiedad conectada:</p>
                                    <p class="font-monospace small fw-semibold mb-0">{{ $status['property_url'] }}</p>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('manager.seo.gsc.disconnect') }}"
                                  id="form-disconnect">
                                @csrf
                                <button type="button" class="btn btn-outline-secondary w-100"
                                        id="btn-disconnect">
                                    Desconectar cuenta Google
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        @endif

        {{-- ── Sección informativa ───────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header border-bottom p-3">
                <h5 class="mb-0 fw-bold">Como funciona la integración</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6 col-lg-3">
                        <div class="d-flex gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle flex-shrink-0 icon-box-40">
                                <i class="fas fa-mouse-pointer text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">Clicks</h6>
                                <p class="text-muted small mb-0">Número de veces que los usuarios hicieron clic en tu sitio desde los resultados.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="d-flex gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center bg-info-subtle flex-shrink-0 icon-box-40">
                                <i class="fas fa-eye text-info"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">Impresiones</h6>
                                <p class="text-muted small mb-0">Cuántas veces apareciste en resultados de búsqueda, aunque no se haya hecho clic.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="d-flex gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center bg-warning-subtle flex-shrink-0 icon-box-40">
                                <i class="fas fa-ranking-star text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">Posicion</h6>
                                <p class="text-muted small mb-0">Posición media en los resultados de búsqueda. Menor número es mejor.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="d-flex gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center bg-success-subtle flex-shrink-0 icon-box-40">
                                <i class="fas fa-percent text-success"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">CTR</h6>
                                <p class="text-muted small mb-0">Click-through rate: proporción de clicks sobre impresiones. Indica la relevancia del snippet.</p>
                            </div>
                        </div>
                    </div>

                </div>

                <hr class="my-3">

                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <div class="rounded-2 d-flex align-items-center justify-content-center bg-secondary-subtle icon-box-36">
                            <i class="fas fa-arrows-rotate text-secondary"></i>
                        </div>
                    </div>
                    <div class="col">
                        <p class="small text-muted mb-0">
                            <strong>Flujo OAuth:</strong>
                            Al hacer clic en "Conectar con Google" serás redirigido a Google para autorizar el acceso.
                            Una vez autorizado, Google devuelve un token que se almacena de forma segura y se usa
                            en cada importación. Puedes revocar el acceso en cualquier momento desde
                            <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener">
                                Permisos de cuenta Google
                            </a>.
                        </p>
                    </div>
                </div>

            </div>
        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/gsc/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/gsc/index.js') }}"></script>
@endpush
