@extends('layouts.managers')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Analytics'])
@endsection

@section('content')

@if(! $configured)

    {{-- Sin habilitar/configurar: antes cada widget del dashboard fallaba su
         propia llamada AJAX por separado (18 endpoints) y mostraba un mosaico
         de "Sin datos"/"Error al cargar"/spinners colgados. Un solo estado
         claro server-side, en vez de eso. --}}
    <div class="widget-content searchable-container list">
        <div class="analytics-empty-card card border-dashed">
            <div class="card-body text-center py-5">
                <div class="analytics-empty-icon mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle">
                    <i class="fas fa-chart-bar text-primary"></i>
                </div>
                <h4 class="fw-bold mb-2">Google Analytics no está configurado</h4>
                <p class="text-muted mb-4">Para ver estadísticas y métricas de tu sitio web, necesitas configurar Google Analytics GA4.</p>

                <div class="analytics-empty-steps text-start mx-auto mb-4 rounded-3 bg-light-secondary p-4">
                    <p class="fw-semibold mb-3">Pasos para configurar:</p>
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">Crea una cuenta de Google Analytics en <a href="https://analytics.google.com" target="_blank" rel="noopener">analytics.google.com</a></li>
                        <li class="mb-2">Obtén tu Property ID (número de 9-10 dígitos)</li>
                        <li class="mb-2">Crea una cuenta de servicio en <a href="https://console.cloud.google.com" target="_blank" rel="noopener">Google Cloud Console</a></li>
                        <li class="mb-2">Descarga el archivo JSON de credenciales</li>
                        <li class="mb-0">Configura los datos en la página de configuración</li>
                    </ul>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                    <a href="{{ route('manager.settings.analytics') }}" class="btn btn-primary px-4">Ir a Configuración</a>
                    <a href="https://github.com/spatie/laravel-analytics#readme" target="_blank" rel="noopener" class="btn btn-outline-secondary px-4">Ver Documentación</a>
                </div>

                <p class="small text-muted mb-0">
                    <i class="fas fa-lock me-1"></i>
                    Tus credenciales se almacenan de forma segura y solo son usadas para consultar estadísticas.
                </p>
            </div>
        </div>
    </div>

@else

    <div class="widget-content searchable-container list" id="analytics-page"
         data-range="{{ $range }}"
         data-geojson-url="{{ asset('vendor/leaflet/countries.geojson') }}"
         data-routes='@php $__jsonInline1 = [
            "overview"       => route("manager.analytics.overview"),
            "comparison"     => route("manager.analytics.comparison"),
            "sessionMetrics" => route("manager.analytics.session-metrics"),
            "topPages"       => route("manager.analytics.top-pages"),
            "topReferrers"   => route("manager.analytics.top-referrers"),
            "browsers"       => route("manager.analytics.browsers"),
            "devices"        => route("manager.analytics.devices"),
            "countries"      => route("manager.analytics.countries"),
            "channels"       => route("manager.analytics.channels"),
            "realtime"       => route("manager.analytics.realtime"),
            "os"             => route("manager.analytics.os"),
            "trafficSources" => route("manager.analytics.traffic-sources"),
            "landingPages"   => route("manager.analytics.landing-pages"),
            "exitPages"      => route("manager.analytics.exit-pages"),
            "channelTrend"   => route("manager.analytics.channel-trend"),
            "hourlyHeatmap"  => route("manager.analytics.hourly-heatmap"),
            "searchTerms"    => route("manager.analytics.search-terms"),
            "userFlow"       => route("manager.analytics.user-flow"),
         ]; @endphp@json($__jsonInline1)'>

        {{-- Filters bar --}}
        <div class="card card-body mb-4 border-0 shadow-sm">
            <div class="row align-items-center g-2">

                <div class="col-auto">
                    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-2 bg-primary-subtle">
                        <span class="realtime-dot position-relative d-flex align-items-center justify-content-center rounded-circle">
                            <span class="realtime-dot-badge position-absolute rounded-circle bg-success border border-white"></span>
                        </span>
                        <div>
                            <div class="d-flex align-items-baseline gap-1 lh-1 mb-1">
                                <span class="fw-bold text-primary" id="realtime-count">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </span>
                                <span class="small fw-semibold text-primary">en línea</span>
                            </div>
                            <div class="lh-1 text-muted realtime-updated-text" id="realtime-updated"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-auto d-none d-md-block">
                    <div class="divider-vertical"></div>
                </div>

                <div class="col analytics-pills-col">
                    <ul class="nav nav-pills gap-1 flex-nowrap overflow-auto" id="rangePills">
                        @foreach([
                            'today'        => 'Hoy',
                            'last_7_days'  => '7 días',
                            'last_30_days' => '30 días',
                            'this_month'   => 'Este mes',
                            'last_month'   => 'Mes anterior',
                            'this_year'    => 'Este año',
                        ] as $key => $label)
                        <li class="nav-item flex-shrink-0">
                            <a class="nav-link py-1 px-3 small fw-semibold {{ $range === $key ? 'active' : 'text-muted' }}"
                               href="#" data-range="{{ $key }}">{{ $label }}</a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div class="col-auto d-flex align-items-center gap-2">
                    <div class="dropdown">
                        <a href="javascript:void(0)"
                           class="icon-btn-32 d-flex align-items-center justify-content-center rounded-circle text-muted"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-vertical"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="javascript:void(0)" id="refreshBtn">Actualizar datos</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)"
                                   data-bs-toggle="modal" data-bs-target="#exportModal">Exportar datos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item btn-print" href="javascript:void(0)">Imprimir</a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

        {{-- KPI cards --}}
        <div class="row mb-4 g-3" id="kpi-row">

            <div class="col-6 col-lg-4">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Sesiones</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-sessions"><span class="spinner-border spinner-border-sm text-muted"></span></h4>
                                <div id="cmp-sessions"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-sessions"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-4">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Usuarios</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-users"><span class="spinner-border spinner-border-sm text-muted"></span></h4>
                                <div id="cmp-users"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-users"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-4">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Vistas de página</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-pageviews"><span class="spinner-border spinner-border-sm text-muted"></span></h4>
                                <div id="cmp-pageviews"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-pageviews"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-4">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Tasa de rebote</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-bounce"><span class="spinner-border spinner-border-sm text-muted"></span></h4>
                                <div id="cmp-bounce"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-bounce"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-4">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Usuarios nuevos</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-new-users"><span class="spinner-border spinner-border-sm text-muted"></span></h4>
                                <p class="fs-3 mb-0 text-muted">registrados</p>
                            </div>
                            <div class="col-4 d-flex justify-content-end">
                                <span class="icon-circle-44 rounded-circle bg-info-subtle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user-plus text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-4">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Duración media</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-duration"><span class="spinner-border spinner-border-sm text-muted"></span></h4>
                                <p class="fs-3 mb-0 text-muted">por sesión</p>
                            </div>
                            <div class="col-4 d-flex justify-content-end">
                                <span class="icon-circle-44 rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-clock text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Charts row: daily + channel trend --}}
        <div class="row mb-4 g-3">
            <div class="col-lg-6">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Sesiones y vistas de página</h4>
                        <p class="card-subtitle mt-1">Tendencia diaria del período seleccionado</p>
                    </div>
                    <div class="card-body">
                        <div id="daily-chart" class="analytics-main-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Tendencia por canal</h4>
                        <p class="card-subtitle mt-1">Sesiones por medio de tráfico</p>
                    </div>
                    <div class="card-body">
                        <div id="channel-trend-chart" class="analytics-main-chart"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Heatmap --}}
        <div class="row mb-4 g-3">
            <div class="col-12">
                <div class="card w-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Actividad por hora</h4>
                        <p class="card-subtitle mt-1">Sesiones por día de la semana y hora del día</p>
                    </div>
                    <div class="card-body px-2 px-md-3">
                        <div id="heatmap-chart" class="analytics-heatmap"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Map + Countries --}}
        <div class="row mb-4 g-3">
            <div class="col-12">
                <div class="card w-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Distribución geográfica</h4>
                        <p class="card-subtitle mt-1">Sesiones por país</p>
                    </div>
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <div class="col-12 col-lg-8 position-relative">
                                <div id="visits-map"></div>
                                <button class="btn btn-sm btn-light border map-reset-btn" id="mapResetBtn" title="Centrar mapa">
                                    <i class="fas fa-compress-arrows-alt"></i>
                                </button>
                            </div>
                            <div class="col-12 col-lg-4 analytics-countries-col">
                                <div id="countries-list"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Devices + Browsers + OS --}}
        <div class="row mb-4 g-3">
            <div class="col-6 col-lg-4">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Dispositivos</h4>
                        <p class="card-subtitle mt-1">Por sesiones</p>
                    </div>
                    <div class="card-body">
                        <div id="devices-chart" class="mb-4 analytics-donut-chart"></div>
                        <hr>
                        <div id="devices-list"></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Navegadores</h4>
                        <p class="card-subtitle mt-1">Por vistas de página</p>
                    </div>
                    <div class="card-body">
                        <div id="browser-chart" class="mb-4 analytics-donut-chart"></div>
                        <hr>
                        <div id="browser-list"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Sistemas operativos</h4>
                        <p class="card-subtitle mt-1">Por sesiones</p>
                    </div>
                    <div class="card-body">
                        <div id="os-list"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Traffic sources --}}
        <div class="row mb-4 g-3">
            <div class="col-12">
                <div class="card w-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Fuentes de tráfico</h4>
                        <p class="card-subtitle mt-1">Origen y medio del tráfico por sesiones</p>
                    </div>
                    <div class="card-body">
                        <div id="traffic-sources-list"></div>
                        <div id="traffic-sources-pager" class="px-1 pt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pages (tabs) + Referrers --}}
        <div class="row mb-4 g-3">
            <div class="col-12">
                <div class="card w-100">
                    <div class="card-header d-md-flex align-items-center">
                        <div>
                            <h4 class="card-title fw-semibold mb-0">Páginas</h4>
                            <p class="card-subtitle mt-1">Análisis de páginas visitadas</p>
                        </div>
                        <div class="ms-auto mt-3 mt-md-0 tabs-scroll-wrapper">
                            <ul class="nav nav-tabs border-0 flex-nowrap tabs-scroll-nav" id="pagesTabs">
                                <li class="nav-item">
                                    <a class="nav-link rounded active" data-bs-toggle="tab" href="#tab-top-pages">Más visitadas</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link rounded" data-bs-toggle="tab" href="#tab-landing-pages">Entrada</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link rounded" data-bs-toggle="tab" href="#tab-exit-pages">Salida</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link rounded" data-bs-toggle="tab" href="#tab-referrers">Referrers</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="tab-top-pages">
                                <div id="pages-list"></div>
                                <div id="pages-pager" class="px-1 pt-2"></div>
                            </div>
                            <div class="tab-pane fade" id="tab-landing-pages">
                                <div id="landing-pages-list"></div>
                                <div id="landing-pages-pager" class="px-1 pt-2"></div>
                            </div>
                            <div class="tab-pane fade" id="tab-exit-pages">
                                <div id="exit-pages-list"></div>
                                <div id="exit-pages-pager" class="px-1 pt-2"></div>
                            </div>
                            <div class="tab-pane fade" id="tab-referrers">
                                <div id="referrers-list"></div>
                                <div id="referrers-pager" class="px-1 pt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search terms + User flow --}}
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Términos de búsqueda</h4>
                        <p class="card-subtitle mt-1">Búsquedas internas del sitio</p>
                    </div>
                    <div class="card-body">
                        <div id="search-terms-list"></div>
                        <div id="search-terms-pager" class="px-1 pt-2"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Flujo de usuarios</h4>
                        <p class="card-subtitle mt-1">Entrada → salida con tasa de rebote</p>
                    </div>
                    <div class="card-body">
                        <div id="user-flow-list"></div>
                        <div id="user-flow-pager" class="px-1 pt-2"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Export modal --}}
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold mb-1">Exportar datos</h5>
                        <p class="text-muted small mb-0">
                            Período: <strong id="export-period-label" class="text-dark">seleccionado</strong>
                        </p>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    @php
                        $exports = [
                            ['key' => 'overview',  'icon' => 'fa-chart-line',   'label' => 'Resumen general',       'desc' => 'Sesiones, usuarios y vistas por día'],
                            ['key' => 'pages',     'icon' => 'fa-file-alt',     'label' => 'Páginas más visitadas', 'desc' => 'URL, título y número de vistas'],
                            ['key' => 'countries', 'icon' => 'fa-globe',        'label' => 'Visitas por país',      'desc' => 'Sesiones y porcentaje por país'],
                            ['key' => 'channels',  'icon' => 'fa-layer-group',  'label' => 'Canales de tráfico',    'desc' => 'Origen del tráfico por canal'],
                        ];
                    @endphp
                    <div class="d-flex flex-column gap-2">
                        @foreach($exports as $e)
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 border">
                            <div class="export-icon-box d-flex align-items-center justify-content-center rounded-2 flex-shrink-0 bg-primary-subtle">
                                <i class="fas {{ $e['icon'] }} text-primary"></i>
                            </div>
                            <div class="export-item-body">
                                <div class="fw-semibold small">{{ $e['label'] }}</div>
                                <div class="text-muted export-desc-text">{{ $e['desc'] }}</div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <button class="btn btn-sm btn-outline-secondary export-btn-sm btn-export"
                                        data-key="{{ $e['key'] }}" data-format="json">JSON</button>
                                <button class="btn btn-sm btn-primary export-btn-sm btn-export"
                                        data-key="{{ $e['key'] }}" data-format="csv">CSV</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endif

@endsection


@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/analytics/index.css') }}">
@endpush

@if($configured)
    @push('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="{{ asset('managers/js/views/analytics/index.js') }}"></script>
    @endpush
@endif
