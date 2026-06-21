@extends('layouts.managers')

@section('content')


    <div class="widget-content searchable-container list">

        {{-- Filters bar --}}
        <div class="card card-body mb-4 border-0 shadow-sm">
            <div class="row align-items-center g-2">

                <div class="col-auto">
                    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-2 bg-primary-subtle">
                        <span class="position-relative d-flex align-items-center justify-content-center rounded-circle"
                              style="width:10px;height:10px;background:#008bce;">
                            <span class="position-absolute rounded-circle bg-success border border-white"
                                  style="width:8px;height:8px;top:-1px;right:-1px;"></span>
                        </span>
                        <div>
                            <div class="d-flex align-items-baseline gap-1 lh-1 mb-1">
                                <span class="fw-bold text-primary" id="realtime-count">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </span>
                                <span class="small fw-semibold text-primary">en línea</span>
                            </div>
                            <div class="lh-1 text-muted" style="font-size:0.7rem;" id="realtime-updated"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-auto d-none d-md-block">
                    <div style="width:1px;height:36px;background:#e9ecef;"></div>
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
                           class="d-flex align-items-center justify-content-center rounded-circle text-muted"
                           style="width:32px;height:32px;background:#f5f6f8;"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-vertical"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="javascript:void(0)" id="refreshBtn">Actualizar datos</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)"
                                   data-bs-toggle="modal" data-bs-target="#exportModal">Exportar datos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="window.print()">Imprimir</a></li>
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
                                <span class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
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
                                <span class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
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
                        <div class="ms-auto mt-3 mt-md-0" style="max-width:100%;overflow-x:auto;">
                            <ul class="nav nav-tabs border-0 flex-nowrap" id="pagesTabs" style="min-width:max-content;">
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
                            <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0 bg-primary-subtle"
                                 style="width:40px;height:40px;">
                                <i class="fas {{ $e['icon'] }} text-primary"></i>
                            </div>
                            <div style="min-width:0;flex:1;">
                                <div class="fw-semibold small">{{ $e['label'] }}</div>
                                <div class="text-muted" style="font-size:0.72rem;">{{ $e['desc'] }}</div>
                            </div>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <button class="btn btn-sm btn-outline-secondary" style="font-size:0.72rem;padding:3px 8px;"
                                        onclick="exportData('{{ $e['key'] }}','json')">JSON</button>
                                <button class="btn btn-sm btn-primary" style="font-size:0.72rem;padding:3px 8px;"
                                        onclick="exportData('{{ $e['key'] }}','csv')">CSV</button>
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

@endsection

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
    /* Base */
    #visits-map { height: 340px; border-radius: 0 0 0 8px; }
    #countries-list { max-height: 340px; overflow-y: auto; }
    .analytics-main-chart { height: 295px; }
    .analytics-heatmap { height: 200px; }
    .analytics-donut-chart { height: 200px; }
    .analytics-countries-col { border-left: 1px solid #dee2e6; }

    .country-item { padding: 10px 16px; border-bottom: 1px solid #f5f6f8; display: flex; align-items: center; gap: 12px; }
    .country-item:last-child { border-bottom: none; }
    .country-item:hover { background: #f5f6f8; cursor: pointer; }
    .country-item.country-active { background: #e8f4fd; }
    .map-reset-btn { position: absolute; top: 10px; right: 10px; z-index: 999; }
    .progress-thin { height: 4px; }
    .cmp-up   { color: #198754; }
    .cmp-down { color: #dc3545; }
    .analytics-pills-col { min-width: 0; }
    #rangePills { -webkit-overflow-scrolling: touch; scrollbar-width: none; }
    #rangePills::-webkit-scrollbar { display: none; }
    #rangePills .nav-link.active { background: #008bce; color: #fff !important; }
    #rangePills .nav-link:hover:not(.active) { background: #e8f4fd; color: #008bce; }
    #rangePills .nav-link { white-space: nowrap; }

    /* Tablet (md) */
    @media (max-width: 991.98px) {
        .analytics-countries-col { border-left: 0; border-top: 1px solid #dee2e6; }
        #visits-map { border-radius: 0; }
    }

    /* KPI cards: full-width text + hide sparklines on xs */
    @media (max-width: 575.98px) {
        #kpi-row .col-8 { flex: 0 0 100%; max-width: 100%; }
        #kpi-row .col-4 { display: none !important; }
    }

    /* Mobile (sm) */
    @media (max-width: 767.98px) {
        #visits-map { height: 220px; }
        #countries-list { max-height: 200px; }
        .analytics-main-chart { height: 220px; }
        .analytics-heatmap { height: 150px; }
        .country-item { padding: 8px 12px; gap: 8px; }
        .country-item .fw-semibold { font-size: 0.8rem; }
        .country-item .text-muted { font-size: 0.72rem; }
    }

    /* Extra small */
    @media (max-width: 575.98px) {
        .analytics-donut-chart { height: 160px; }
        .card-header h4.card-title { font-size: 0.95rem; }
        #traffic-sources-list, #pages-list, #landing-pages-list, #exit-pages-list,
        #referrers-list, #search-terms-list, #user-flow-list {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        #traffic-sources-list table, #pages-list table, #landing-pages-list table,
        #exit-pages-list table, #referrers-list table, #search-terms-list table,
        #user-flow-list table {
            min-width: 420px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function () {
    'use strict';

    let selectedRange = '{{ $range }}';
    let countriesGeoData = null, leafletMap = null, geoLayer = null, countrySessions = {};
    let dailyChart = null, browserChart = null, devicesChart = null, channelChart = null, heatmapChart = null;

    const exportCache = {};

    const ROUTES = {
        overview:       '{{ route("manager.analytics.overview") }}',
        comparison:     '{{ route("manager.analytics.comparison") }}',
        sessionMetrics: '{{ route("manager.analytics.session-metrics") }}',
        topPages:       '{{ route("manager.analytics.top-pages") }}',
        topReferrers:   '{{ route("manager.analytics.top-referrers") }}',
        browsers:       '{{ route("manager.analytics.browsers") }}',
        devices:        '{{ route("manager.analytics.devices") }}',
        countries:      '{{ route("manager.analytics.countries") }}',
        channels:       '{{ route("manager.analytics.channels") }}',
        realtime:       '{{ route("manager.analytics.realtime") }}',
        os:             '{{ route("manager.analytics.os") }}',
        trafficSources: '{{ route("manager.analytics.traffic-sources") }}',
        landingPages:   '{{ route("manager.analytics.landing-pages") }}',
        exitPages:      '{{ route("manager.analytics.exit-pages") }}',
        channelTrend:   '{{ route("manager.analytics.channel-trend") }}',
        hourlyHeatmap:  '{{ route("manager.analytics.hourly-heatmap") }}',
        searchTerms:    '{{ route("manager.analytics.search-terms") }}',
        userFlow:       '{{ route("manager.analytics.user-flow") }}',
    };

    const PRIMARY  = '#008bce';
    const PRIMARY2 = '#0065a0';
    const DARK     = '#333333';
    const PALETTE  = [PRIMARY, DARK, PRIMARY2, '#555', '#7ab8d9', '#aaa'];

    function chartH(type) {
        var w = window.innerWidth;
        if (type === 'main')    return w < 768 ? 220 : 295;
        if (type === 'heatmap') return w < 768 ? 150 : 200;
        if (type === 'donut')   return w < 576 ? 160 : 200;
        return 200;
    }

    const channelColors = {
        organic: PRIMARY, cpc: DARK, social: PRIMARY2,
        referral: '#555', email: '#c41c1c', direct: '#888',
        none: '#888', '(none)': '#888'
    };

    // ─── Helpers ──────────────────────────────────────────────────────────
    const fmt = n => new Intl.NumberFormat('es-ES').format(n || 0);

    function fmtSeconds(s) {
        s = Math.round(s || 0);
        const m = Math.floor(s / 60), sec = s % 60;
        return m + 'm ' + String(sec).padStart(2, '0') + 's';
    }

    function escHtml(t) {
        const d = document.createElement('div');
        d.textContent = String(t || '');
        return d.innerHTML;
    }

    const spinner    = () => '<div class="text-center py-4"><span class="spinner-border spinner-border-sm text-muted"></span></div>';
    const emptyState = m  => `<div class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i><small>${escHtml(m)}</small></div>`;

    function cmpBadge(current, previous, invert) {
        invert = invert || false;
        if (!previous || previous === 0) return '';
        const pct  = ((current - previous) / previous * 100).toFixed(1);
        const up   = invert ? parseFloat(pct) < 0 : parseFloat(pct) > 0;
        const icon = up ? 'fa-arrow-up' : 'fa-arrow-down';
        const bg   = up ? 'bg-success-subtle' : 'bg-danger-subtle';
        const txt  = up ? 'text-success'      : 'text-danger';
        const sign = parseFloat(pct) > 0 ? '+' : '';
        return '<div class="d-flex align-items-center">' +
            '<span class="me-1 rounded-circle ' + bg + ' d-flex align-items-center justify-content-center" style="width:20px;height:20px;">' +
                '<i class="fas ' + icon + ' ' + txt + '" style="font-size:0.6rem;"></i>' +
            '</span>' +
            '<p class="text-dark me-1 fs-3 mb-0">' + sign + Math.abs(pct) + '%</p>' +
            '<p class="fs-3 mb-0 text-muted">vs anterior</p>' +
        '</div>';
    }

    // ─── Pagination ────────────────────────────────────────────────────────
    var PER_PAGE = 10;
    var pagerState  = {};
    var pagerRenders = {};

    function getPager(key) {
        if (!pagerState[key]) pagerState[key] = { data: [], page: 1 };
        return pagerState[key];
    }
    function setPagerData(key, data) { getPager(key).data = data; getPager(key).page = 1; }
    function getPageItems(key) {
        var s = getPager(key);
        return s.data.slice((s.page - 1) * PER_PAGE, s.page * PER_PAGE);
    }

    function pagerNav(key) {
        var s = getPager(key);
        var total = s.data.length;
        var totalPages = Math.ceil(total / PER_PAGE);
        if (totalPages <= 1) return '';
        var page  = s.page;
        var from  = (page - 1) * PER_PAGE + 1;
        var to    = Math.min(page * PER_PAGE, total);
        var start = Math.max(1, page - 2);
        var end   = Math.min(totalPages, start + 4);

        function btn(p, label, disabled, active) {
            var base  = 'pager-btn d-inline-flex align-items-center justify-content-center border-0 rounded';
            var style = active
                ? 'background:' + PRIMARY + ';color:#fff;font-weight:600;'
                : (disabled ? 'background:transparent;color:#ccc;cursor:default;' : 'background:transparent;color:#555;');
            return '<button class="' + base + '" style="width:30px;height:30px;font-size:0.8rem;' + style + '"' +
                   ' data-pkey="' + key + '" data-page="' + p + '"' + (disabled ? ' disabled' : '') + '>' + label + '</button>';
        }

        var btns = btn(page - 1, '&#8249;', page <= 1, false);
        for (var i = start; i <= end; i++) btns += btn(i, i, false, i === page);
        btns += btn(page + 1, '&#8250;', page >= totalPages, false);

        return '<div class="d-flex align-items-center justify-content-between pt-1">' +
               '<p class="text-muted">' + from + '–' + to + ' de ' + total + '</p>' +
               '<div class="d-flex align-items-center gap-1">' + btns + '</div></div>';
    }

    $(document).on('click', '.pager-btn', function (e) {
        e.preventDefault();
        var key  = $(this).data('pkey');
        var page = parseInt($(this).data('page'));
        if (!pagerState[key] || pagerState[key].page === page) return;
        pagerState[key].page = page;
        pagerRenders[key]();
    });

    // ─── Realtime ──────────────────────────────────────────────────────────
    function loadRealtime() {
        $.get(ROUTES.realtime)
            .done(function (res) {
                if (!res.success) return;
                $('#realtime-count').text(fmt(res.data.active_users));
                var now = new Date();
                $('#realtime-updated').text('Act. ' + now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
            })
            .fail(function () { $('#realtime-count').text('—'); });
    }

    // ─── Overview + KPIs ──────────────────────────────────────────────────
    var currentTotals = null;

    function loadOverview() {
        $.get(ROUTES.overview, { range: selectedRange })
            .done(function (res) {
                if (!res.success) { setKpiError(); return; }
                var t = res.data.totals;
                currentTotals = t;
                exportCache['overview'] = res.data;

                $('#kpi-sessions').text(fmt(t.sessions));
                $('#kpi-users').text(fmt(t.totalUsers));
                $('#kpi-pageviews').text(fmt(t.screenPageViews));
                $('#kpi-bounce').text(((t.bounceRate || 0) * 100).toFixed(1) + '%');

                renderDailyChart(res.data.chart_data);
                renderSparklines(res.data.chart_data);
                loadComparison();
            })
            .fail(setKpiError);
    }

    function setKpiError() {
        ['sessions', 'users', 'pageviews', 'bounce'].forEach(function(k) { $('#kpi-' + k).text('—'); });
    }

    function loadComparison() {
        if (!currentTotals) return;
        $.get(ROUTES.comparison, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !currentTotals) return;
                var p = res.data;
                $('#cmp-sessions').html(cmpBadge(currentTotals.sessions, p.sessions));
                $('#cmp-users').html(cmpBadge(currentTotals.totalUsers, p.totalUsers));
                $('#cmp-pageviews').html(cmpBadge(currentTotals.screenPageViews, p.screenPageViews));
                $('#cmp-bounce').html(cmpBadge(currentTotals.bounceRate, p.bounceRate, true));
            });
    }

    function loadSessionMetrics() {
        $.get(ROUTES.sessionMetrics, { range: selectedRange })
            .done(function (res) {
                if (!res.success) { $('#kpi-new-users, #kpi-duration').text('—'); return; }
                $('#kpi-new-users').text(fmt(res.data.new_users));
                $('#kpi-duration').text(fmtSeconds(res.data.avg_session_duration));
            })
            .fail(function () { $('#kpi-new-users, #kpi-duration').text('—'); });
    }

    // ─── Daily chart ──────────────────────────────────────────────────────
    function renderDailyChart(chartData) {
        if (!chartData || !chartData.length) {
            $('#daily-chart').html(emptyState('Sin datos para el período'));
            return;
        }
        var months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        var fmtDate = function(d) {
            if (!d || d.length !== 8) return d;
            return months[parseInt(d.slice(4, 6)) - 1] + ' ' + parseInt(d.slice(6, 8));
        };
        var dates    = chartData.map(function(r) { return fmtDate(r.date || ''); });
        var sessions = chartData.map(function(r) { return parseInt(r.sessions || 0); });
        var views    = chartData.map(function(r) { return parseInt(r.screenPageViews || 0); });

        if (dailyChart) { dailyChart.destroy(); dailyChart = null; }
        $('#daily-chart').html('');

        dailyChart = new ApexCharts(document.querySelector('#daily-chart'), {
            series: [
                { name: 'Sesiones', data: sessions },
                { name: 'Vistas de página', data: views },
            ],
            chart: { type: 'area', height: chartH('main'), toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'inherit' },
            colors: [PRIMARY, DARK],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.15, opacityTo: 0.02, stops: [0, 100] } },
            xaxis: {
                categories: dates,
                labels: { rotate: -30, style: { fontSize: '11px', colors: '#adb5bd' } },
                axisBorder: { show: false }, axisTicks: { show: false },
            },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#adb5bd' }, formatter: function(v) { return Math.round(v); } } },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
            tooltip: { theme: 'light', shared: true, intersect: false },
            legend: { position: 'top', horizontalAlign: 'right', fontFamily: 'inherit' },
            markers: { size: 0 },
        });
        dailyChart.render();
    }

    // ─── Sparklines ────────────────────────────────────────────────────────
    var _sparks = {};

    function renderSparklines(chartData) {
        if (!chartData || !chartData.length) return;

        var spW = window.innerWidth < 576 ? 55 : 80;
        function mkCfg(data, color, type) {
            return {
                series: [{ data: data }],
                chart: { type: type, height: 70, width: spW, sparkline: { enabled: true }, animations: { enabled: false }, fontFamily: 'inherit' },
                colors: [color],
                stroke: { curve: 'smooth', width: 2 },
                fill: type === 'area'
                    ? { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } }
                    : { type: 'solid' },
                tooltip: { fixed: { enabled: false }, x: { show: false }, y: { title: { formatter: function() { return ''; } } } },
                plotOptions: { bar: { borderRadius: 2, columnWidth: '60%' } },
            };
        }

        var defs = {
            '#spark-sessions':  { data: chartData.map(function(r) { return r.sessions  || 0; }),                                      color: PRIMARY,  type: 'area' },
            '#spark-users':     { data: chartData.map(function(r) { return r.totalUsers || 0; }),                                      color: DARK,     type: 'bar'  },
            '#spark-pageviews': { data: chartData.map(function(r) { return r.screenPageViews || 0; }),                                 color: PRIMARY2, type: 'bar'  },
            '#spark-bounce':    { data: chartData.map(function(r) { return parseFloat(((r.bounceRate || 0) * 100).toFixed(1)); }),     color: '#555',   type: 'area' },
        };

        Object.keys(defs).forEach(function(sel) {
            var cfg = defs[sel];
            var el = document.querySelector(sel);
            if (!el) return;
            if (_sparks[sel]) _sparks[sel].destroy();
            _sparks[sel] = new ApexCharts(el, mkCfg(cfg.data, cfg.color, cfg.type));
            _sparks[sel].render();
        });
    }

    // ─── Channel trend ─────────────────────────────────────────────────────
    function loadChannelTrend() {
        $.get(ROUTES.channelTrend, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.dates || !res.data.dates.length) {
                    $('#channel-trend-chart').html(emptyState('Sin datos de canales'));
                    return;
                }
                var d = res.data;
                if (channelChart) { channelChart.destroy(); channelChart = null; }
                $('#channel-trend-chart').html('');

                channelChart = new ApexCharts(document.querySelector('#channel-trend-chart'), {
                    series: d.series,
                    chart: { type: 'area', height: chartH('main'), stacked: true, toolbar: { show: false }, zoom: { enabled: false } },
                    colors: d.series.map(function(s) { return channelColors[s.name.toLowerCase()] || '#adb5bd'; }),
                    stroke: { curve: 'smooth', width: 1 },
                    fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.1 } },
                    xaxis: { categories: d.dates, labels: { rotate: -30, style: { fontSize: '11px' } } },
                    yaxis: { labels: { formatter: function(v) { return Math.round(v); } } },
                    grid: { borderColor: '#f0f0f0' },
                    tooltip: { theme: 'light', shared: true },
                    legend: { position: 'top' },
                });
                channelChart.render();
            })
            .fail(function() { $('#channel-trend-chart').html(emptyState('Sin datos')); });
    }

    // ─── Hourly heatmap ────────────────────────────────────────────────────
    function loadHourlyHeatmap() {
        $.get(ROUTES.hourlyHeatmap, { range: selectedRange })
            .done(function (res) {
                if (!res.success) { $('#heatmap-chart').html(emptyState('Sin datos')); return; }
                var matrix = res.data.matrix, days = res.data.days;
                if (!matrix) return;

                var series = days.map(function(day, dayIdx) {
                    return {
                        name: day,
                        data: matrix[dayIdx].map(function(val, hour) {
                            return { x: String(hour).padStart(2, '0') + 'h', y: val };
                        })
                    };
                });

                if (heatmapChart) { heatmapChart.destroy(); heatmapChart = null; }
                $('#heatmap-chart').html('');

                heatmapChart = new ApexCharts(document.querySelector('#heatmap-chart'), {
                    series: series,
                    chart: { type: 'heatmap', height: chartH('heatmap'), toolbar: { show: false } },
                    dataLabels: { enabled: false },
                    colors: [PRIMARY],
                    xaxis: { labels: { style: { fontSize: '10px' } } },
                    tooltip: { theme: 'light', y: { formatter: function(v) { return fmt(v) + ' sesiones'; } } },
                    legend: { show: false },
                });
                heatmapChart.render();
            })
            .fail(function() { $('#heatmap-chart').html(emptyState('Sin datos')); });
    }

    // ─── Map ───────────────────────────────────────────────────────────────
    var countryNameMap = {
        'United States': 'United States of America',
        'Czech Republic': 'Czechia',
        'Tanzania': 'United Republic of Tanzania',
    };

    var countryFlags = {
        'Argentina':'🇦🇷','Australia':'🇦🇺','Austria':'🇦🇹','Belgium':'🇧🇪','Bolivia':'🇧🇴',
        'Brazil':'🇧🇷','Canada':'🇨🇦','Chile':'🇨🇱','China':'🇨🇳','Colombia':'🇨🇴',
        'Costa Rica':'🇨🇷','Cuba':'🇨🇺','Denmark':'🇩🇰','Dominican Republic':'🇩🇴','Ecuador':'🇪🇨',
        'Egypt':'🇪🇬','El Salvador':'🇸🇻','France':'🇫🇷','Germany':'🇩🇪','Guatemala':'🇬🇹',
        'Honduras':'🇭🇳','India':'🇮🇳','Indonesia':'🇮🇩','Ireland':'🇮🇪','Italy':'🇮🇹',
        'Japan':'🇯🇵','Malaysia':'🇲🇾','Mexico':'🇲🇽','Morocco':'🇲🇦','Netherlands':'🇳🇱',
        'New Zealand':'🇳🇿','Nicaragua':'🇳🇮','Nigeria':'🇳🇬','Norway':'🇳🇴','Pakistan':'🇵🇰',
        'Panama':'🇵🇦','Paraguay':'🇵🇾','Peru':'🇵🇪','Philippines':'🇵🇭','Poland':'🇵🇱',
        'Portugal':'🇵🇹','Romania':'🇷🇴','Russia':'🇷🇺','Saudi Arabia':'🇸🇦','Spain':'🇪🇸',
        'Sweden':'🇸🇪','Switzerland':'🇨🇭','Turkey':'🇹🇷','Ukraine':'🇺🇦',
        'United Arab Emirates':'🇦🇪','United Kingdom':'🇬🇧','United States':'🇺🇸',
        'Uruguay':'🇺🇾','Venezuela':'🇻🇪','Vietnam':'🇻🇳',
    };

    var countryLayers = {};

    function getColor(sessions, max) {
        if (!sessions) return '#f0f0f0';
        var r = sessions / max;
        if (r > 0.75) return '#005b9f';
        if (r > 0.5)  return PRIMARY;
        if (r > 0.25) return PRIMARY2;
        if (r > 0.1)  return '#5ba5d5';
        if (r > 0.02) return '#a8d0ed';
        return '#dbeef9';
    }

    function initMap() {
        if (leafletMap) { leafletMap.remove(); leafletMap = null; }
        leafletMap = L.map('visits-map', { zoomControl: false, scrollWheelZoom: false, attributionControl: false }).setView([20, 0], 1.5);
        L.control.zoom({ position: 'bottomright' }).addTo(leafletMap);

        $('#mapResetBtn').on('click', function () {
            leafletMap.flyTo([20, 0], 1.5, { duration: 0.8 });
            $('.country-item').removeClass('country-active');
        });
    }

    function zoomToCountry(apiName) {
        var geoName = countryNameMap[apiName] || apiName;
        var layer   = countryLayers[geoName] || countryLayers[apiName];
        if (!layer) return;
        leafletMap.flyToBounds(layer.getBounds(), { padding: [30, 30], duration: 0.9, maxZoom: 6 });
        $('.country-item').removeClass('country-active');
        $('.country-item[data-country="' + CSS.escape(apiName) + '"]').addClass('country-active');
    }

    function drawGeoLayer(geoJson, maxSessions) {
        if (geoLayer) leafletMap.removeLayer(geoLayer);
        Object.keys(countryLayers).forEach(function(k) { delete countryLayers[k]; });

        geoLayer = L.geoJson(geoJson, {
            style: function(feature) {
                var name = feature.properties.ADMIN || feature.properties.name;
                return { fillColor: getColor(countrySessions[name] || 0, maxSessions), weight: 0.5, color: '#fff', fillOpacity: 0.85 };
            },
            onEachFeature: function(feature, layer) {
                var name     = feature.properties.ADMIN || feature.properties.name;
                var sessions = countrySessions[name] || 0;
                countryLayers[name] = layer;

                var tip = sessions
                    ? '<strong>' + name + '</strong><br><span style="color:' + PRIMARY + '">' + fmt(sessions) + '</span> sesiones'
                    : '<strong>' + name + '</strong><br><span class="text-muted">Sin visitas</span>';
                layer.bindTooltip(tip, { sticky: true });

                layer.on('mouseover', function () {
                    if (sessions) this.setStyle({ weight: 2, color: PRIMARY, fillOpacity: 1 });
                });
                layer.on('mouseout', function () { geoLayer.resetStyle(this); });
                layer.on('click', function () {
                    if (!sessions) return;
                    leafletMap.flyToBounds(layer.getBounds(), { padding: [30, 30], duration: 0.9, maxZoom: 6 });
                    var apiName = Object.keys(countryNameMap).find(function(k) { return countryNameMap[k] === name; }) || name;
                    $('.country-item').removeClass('country-active');
                    $('.country-item[data-country="' + CSS.escape(apiName) + '"]').addClass('country-active');
                });
            }
        }).addTo(leafletMap);
    }

    function loadCountries() {
        $('#countries-list').html(spinner());
        $.get(ROUTES.countries, { range: selectedRange })
            .done(function (res) {
                if (!res.success) return;
                exportCache['countries'] = res.data;
                countrySessions = {};
                res.data.forEach(function(d) {
                    countrySessions[countryNameMap[d.country] || d.country] = d.sessions;
                    countrySessions[d.country] = d.sessions;
                });
                var maxSessions = res.data.length ? Math.max.apply(null, res.data.map(function(d) { return d.sessions; })) : 1;

                if (countriesGeoData) drawGeoLayer(countriesGeoData, maxSessions);
                else {
                    $.getJSON('{{ asset("vendor/leaflet/countries.geojson") }}')
                        .done(function(geoJson) { countriesGeoData = geoJson; drawGeoLayer(geoJson, maxSessions); })
                        .fail(function() { $('#visits-map').html('<div class="text-center py-5 text-muted">No se pudo cargar el mapa</div>'); });
                }

                var rankColor = function(i) { return i === 0 ? PRIMARY : i === 1 ? PRIMARY2 : i === 2 ? '#7ab8d9' : '#ccc'; };
                var html = res.data.slice(0, 15).map(function(c, i) {
                    return '<div class="country-item" data-country="' + escHtml(c.country) + '">' +
                        '<span class="fw-bold flex-shrink-0 text-end" style="font-size:0.75rem;width:18px;color:' + rankColor(i) + ';">' + (i + 1) + '</span>' +
                        '<span style="font-size:1.2rem;">' + (countryFlags[c.country] || '🌐') + '</span>' +
                        '<div style="min-width:0;flex:1;">' +
                            '<div class="fw-semibold text-truncate mb-1" style="font-size:0.82rem;">' + escHtml(c.country) + '</div>' +
                            '<div class="progress rounded-pill" style="height:5px;">' +
                                '<div class="progress-bar rounded-pill" style="width:' + c.percentage + '%;background:' + PRIMARY + ';"></div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="text-end flex-shrink-0">' +
                            '<div class="fw-bold lh-1" style="font-size:0.9rem;color:' + PRIMARY + ';">' + fmt(c.sessions) + '</div>' +
                            '<div class="text-muted lh-1 mt-1" style="font-size:0.7rem;">' + c.percentage + '%</div>' +
                        '</div>' +
                    '</div>';
                }).join('');
                $('#countries-list').html(html);

                $(document).on('click', '.country-item', function () {
                    zoomToCountry($(this).data('country'));
                });
            });
    }

    // ─── Devices ───────────────────────────────────────────────────────────
    var deviceIcons  = { desktop: 'fas fa-desktop', mobile: 'fas fa-mobile-alt', tablet: 'fas fa-tablet-alt' };
    var deviceLabels = { desktop: 'Escritorio', mobile: 'Móvil', tablet: 'Tablet' };

    function loadDevices() {
        $('#devices-chart').html(spinner()); $('#devices-list').html('');
        $.get(ROUTES.devices, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#devices-chart').html(emptyState('Sin datos')); return; }
                var data = res.data;

                if (devicesChart) { devicesChart.destroy(); devicesChart = null; }
                $('#devices-chart').html('');
                devicesChart = new ApexCharts(document.querySelector('#devices-chart'), {
                    series: data.map(function(d) { return d.sessions; }),
                    labels: data.map(function(d) { return d.device; }),
                    chart: { type: 'donut', height: chartH('donut'), fontFamily: 'inherit' },
                    colors: PALETTE.slice(0, data.length),
                    legend: { show: false },
                    dataLabels: { enabled: false },
                    tooltip: { y: { formatter: function(v) { return fmt(v); } } },
                    plotOptions: { pie: { donut: { size: '72%' } } },
                });
                devicesChart.render();

                $('#devices-list').html(data.map(function(d, i) {
                    var key   = d.device.toLowerCase();
                    var icon  = deviceIcons[key]  || 'fas fa-question-circle';
                    var label = deviceLabels[key] || escHtml(d.device);
                    return '<div class="d-flex align-items-center justify-content-between ' + (i < data.length - 1 ? 'mb-3' : '') + '">' +
                        '<div class="d-flex align-items-center gap-3">' +
                            '<div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle" style="width:36px;height:36px;flex-shrink:0;">' +
                                '<i class="' + icon + ' text-primary"></i>' +
                            '</div>' +
                            '<div>' +
                                '<h6 class="mb-0 fw-semibold">' + label + '</h6>' +
                                '<p class="fs-3 mb-0 text-muted">' + fmt(d.sessions) + ' sesiones</p>' +
                            '</div>' +
                        '</div>' +
                        '<span class="fw-bold" style="font-size:0.85rem;">' + d.percentage + '%</span>' +
                    '</div>';
                }).join(''));
            })
            .fail(function() { $('#devices-chart').html(emptyState('Error al cargar')); });
    }

    // ─── Browsers ──────────────────────────────────────────────────────────
    var browserIconMap = {
        chrome: 'fab fa-chrome', safari: 'fab fa-safari',
        firefox: 'fab fa-firefox-browser', edge: 'fab fa-edge',
        opera: 'fab fa-opera', samsung: 'fas fa-mobile-alt',
    };

    function loadBrowsers() {
        $('#browser-chart').html(spinner()); $('#browser-list').html('');
        $.get(ROUTES.browsers, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#browser-chart').html(emptyState('Sin datos')); return; }
                var data = res.data;

                if (browserChart) { browserChart.destroy(); browserChart = null; }
                $('#browser-chart').html('');
                browserChart = new ApexCharts(document.querySelector('#browser-chart'), {
                    series: data.map(function(b) { return b.sessions; }),
                    labels: data.map(function(b) { return b.name; }),
                    chart: { type: 'donut', height: chartH('donut'), fontFamily: 'inherit' },
                    colors: PALETTE.slice(0, data.length),
                    legend: { show: false },
                    dataLabels: { enabled: false },
                    tooltip: { y: { formatter: function(v) { return fmt(v); } } },
                    plotOptions: { pie: { donut: { size: '72%' } } },
                });
                browserChart.render();

                $('#browser-list').html(data.slice(0, 5).map(function(b, i) {
                    var key  = b.name.toLowerCase().replace(/\s+/g, '');
                    var icon = browserIconMap[key] || 'fas fa-globe';
                    return '<div class="d-flex align-items-center justify-content-between ' + (i < 4 ? 'mb-3' : '') + '">' +
                        '<div class="d-flex align-items-center gap-3">' +
                            '<div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle" style="width:36px;height:36px;flex-shrink:0;">' +
                                '<i class="' + icon + ' text-primary"></i>' +
                            '</div>' +
                            '<div>' +
                                '<h6 class="mb-0 fw-semibold">' + escHtml(b.name) + '</h6>' +
                                '<p class="fs-3 mb-0 text-muted">' + fmt(b.sessions) + ' vistas</p>' +
                            '</div>' +
                        '</div>' +
                        '<span class="fw-bold" style="font-size:0.85rem;">' + b.percentage + '%</span>' +
                    '</div>';
                }).join(''));
            })
            .fail(function() { $('#browser-chart').html(emptyState('Error al cargar')); });
    }

    // ─── OS ────────────────────────────────────────────────────────────────
    var osIconMap = {
        windows: 'fab fa-windows', android: 'fab fa-android',
        ios: 'fab fa-apple', macos: 'fab fa-apple',
        linux: 'fab fa-linux', chrome: 'fab fa-chrome',
    };

    function loadOS() {
        $('#os-list').html(spinner());
        $.get(ROUTES.os, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#os-list').html(emptyState('Sin datos')); return; }
                $('#os-list').html(res.data.map(function(o, i) {
                    var key  = o.os.toLowerCase().replace(/\s+/g, '');
                    var icon = osIconMap[key] || 'fas fa-laptop';
                    return '<div class="d-flex align-items-center justify-content-between ' + (i < res.data.length - 1 ? 'mb-3' : '') + '">' +
                        '<div class="d-flex align-items-center gap-3">' +
                            '<div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle" style="width:36px;height:36px;flex-shrink:0;">' +
                                '<i class="' + icon + ' text-primary"></i>' +
                            '</div>' +
                            '<div>' +
                                '<h6 class="mb-0 fw-semibold">' + escHtml(o.os) + '</h6>' +
                                '<p class="fs-3 mb-0 text-muted">' + fmt(o.sessions) + ' sesiones</p>' +
                            '</div>' +
                        '</div>' +
                        '<span class="fw-bold" style="font-size:0.85rem;">' + o.percentage + '%</span>' +
                    '</div>';
                }).join(''));
            })
            .fail(function() { $('#os-list').html(emptyState('Error al cargar')); });
    }

    // ─── Traffic sources ───────────────────────────────────────────────────
    var termBadges = ['bg-primary-subtle text-primary','bg-success-subtle text-success','bg-warning-subtle text-warning','bg-info-subtle text-info','bg-danger-subtle text-danger'];

    function loadTrafficSources() {
        $('#traffic-sources-list').html(spinner()); $('#traffic-sources-pager').html('');
        $.get(ROUTES.trafficSources, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#traffic-sources-list').html(emptyState('Sin datos')); return; }
                exportCache['channels'] = res.data;
                setPagerData('trafficSources', res.data);
                pagerRenders['trafficSources'] = function () {
                    var rows = getPageItems('trafficSources').map(function(s, i) {
                        var badge = termBadges[i % termBadges.length];
                        var label = s.source === '(direct)' ? 'Directo' : escHtml(s.source);
                        var med   = (s.medium === 'none' || s.medium === '(none)' || !s.medium) ? 'directo' : escHtml(s.medium);
                        var num   = (getPager('trafficSources').page - 1) * PER_PAGE + i + 1;
                        return '<tr>' +
                            '<td class="ps-0">' +
                                '<div class="d-flex align-items-center gap-2">' +
                                    '<span class="badge bg-secondary-subtle text-secondary">' + num + '</span>' +
                                    '<div>' +
                                        '<div class="fw-semibold small">' + label + '</div>' +
                                        '<p class="text-muted">' + med + '</p>' +
                                    '</div>' +
                                '</div>' +
                            '</td>' +
                            '<td><span class="badge fw-semibold py-1 ' + badge + '">' + fmt(s.sessions) + '</span></td>' +
                            '<td>' +
                                '<div class="d-flex align-items-center gap-2">' +
                                    '<div class="progress progress-thin flex-fill" style="min-width:50px;">' +
                                        '<div class="progress-bar bg-primary" style="width:' + s.percentage + '%;"></div>' +
                                    '</div>' +
                                    '<p class="text-muted">' + s.percentage + '%</p>' +
                                '</div>' +
                            '</td>' +
                        '</tr>';
                    }).join('');
                    $('#traffic-sources-list').html(
                        '<table class="table align-middle text-nowrap mb-0">' +
                            '<thead><tr class="text-muted fw-semibold">' +
                                '<th scope="col" class="ps-0">Fuente</th>' +
                                '<th scope="col">Sesiones</th>' +
                                '<th scope="col">Participación</th>' +
                            '</tr></thead>' +
                            '<tbody class="border-top">' + rows + '</tbody>' +
                        '</table>'
                    );
                    $('#traffic-sources-pager').html(pagerNav('trafficSources'));
                };
                pagerRenders['trafficSources']();
            })
            .fail(function() { $('#traffic-sources-list').html(emptyState('Sin datos')); });
    }

    // ─── Pages helper ──────────────────────────────────────────────────────
    function pagesTable(rows, cols, headers) {
        var thead = '<thead><tr class="text-muted fw-semibold">' + headers.map(function(h, i) {
            return '<th scope="col" class="' + (i === 0 ? 'ps-0' : '') + (h.end ? ' text-end' : '') + '">' + h.label + '</th>';
        }).join('') + '</tr></thead>';
        var body  = rows.map(function(r) {
            return '<tr>' + cols.map(function(c, i) {
                return '<td class="' + (i === 0 ? 'ps-0' : '') + (c.end ? ' text-end' : '') + '">' + c.render(r) + '</td>';
            }).join('') + '</tr>';
        }).join('');
        return '<table class="table align-middle mb-0 text-nowrap">' + thead + '<tbody class="border-top">' + body + '</tbody></table>';
    }

    function loadTopPages() {
        $('#pages-list').html(spinner()); $('#pages-pager').html('');
        $.get(ROUTES.topPages, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#pages-list').html(emptyState('Sin datos')); return; }
                exportCache['pages'] = res.data;
                setPagerData('topPages', res.data);
                pagerRenders['topPages'] = function () {
                    $('#pages-list').html(pagesTable(
                        getPageItems('topPages'),
                        [
                            { render: function(p) { return '<h6 class="mb-0 fw-semibold text-truncate" style="max-width:400px;">' + escHtml(p.title) + '</h6><span class="fs-2 text-muted text-truncate d-block" style="max-width:400px;">' + escHtml(p.url) + '</span>'; } },
                            { end: true, render: function(p) { return '<span class="badge bg-primary-subtle text-primary rounded-pill">' + fmt(p.views) + '</span>'; } },
                        ],
                        [{ label: 'Página' }, { label: 'Vistas', end: true }]
                    ));
                    $('#pages-pager').html(pagerNav('topPages'));
                };
                pagerRenders['topPages']();
            });
    }

    function loadLandingPages() {
        $('#landing-pages-list').html(spinner()); $('#landing-pages-pager').html('');
        $.get(ROUTES.landingPages, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#landing-pages-list').html(emptyState('Sin datos')); return; }
                setPagerData('landingPages', res.data);
                pagerRenders['landingPages'] = function () {
                    $('#landing-pages-list').html(pagesTable(
                        getPageItems('landingPages'),
                        [
                            { render: function(p) { return '<h6 class="mb-0 fw-semibold small text-truncate" style="max-width:400px;">' + escHtml(p.page) + '</h6>'; } },
                            { end: true, render: function(p) { return '<span class="badge bg-success-subtle text-success rounded-pill">' + fmt(p.sessions) + '</span>'; } },
                            { end: true, render: function(p) { return '<p class="text-muted">' + p.bounce_rate + '%</p>'; } },
                        ],
                        [{ label: 'Página de entrada' }, { label: 'Sesiones', end: true }, { label: 'Rebote', end: true }]
                    ));
                    $('#landing-pages-pager').html(pagerNav('landingPages'));
                };
                pagerRenders['landingPages']();
            });
    }

    function loadExitPages() {
        $('#exit-pages-list').html(spinner()); $('#exit-pages-pager').html('');
        $.get(ROUTES.exitPages, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#exit-pages-list').html(emptyState('Sin datos')); return; }
                setPagerData('exitPages', res.data);
                pagerRenders['exitPages'] = function () {
                    $('#exit-pages-list').html(pagesTable(
                        getPageItems('exitPages'),
                        [
                            { render: function(p) { return '<h6 class="mb-0 fw-semibold small text-truncate" style="max-width:400px;">' + escHtml(p.page) + '</h6>'; } },
                            { end: true, render: function(p) { return '<span class="badge bg-warning-subtle text-warning rounded-pill">' + fmt(p.sessions) + '</span>'; } },
                            { end: true, render: function(p) { return '<p class="text-muted">' + fmt(p.pageviews) + '</p>'; } },
                        ],
                        [{ label: 'Página de salida' }, { label: 'Sesiones', end: true }, { label: 'Vistas', end: true }]
                    ));
                    $('#exit-pages-pager').html(pagerNav('exitPages'));
                };
                pagerRenders['exitPages']();
            });
    }

    // ─── Referrers ─────────────────────────────────────────────────────────
    var mediumBg    = { organic:'bg-success-subtle', cpc:'bg-primary-subtle', social:'bg-info-subtle', referral:'bg-warning-subtle', email:'bg-danger-subtle', direct:'bg-secondary-subtle', none:'bg-secondary-subtle' };
    var mediumColor = { organic:'text-success', cpc:'text-primary', social:'text-info', referral:'text-warning', email:'text-danger', direct:'text-secondary', none:'text-secondary' };
    var mediumLabel = { organic:'Búsqueda orgánica', cpc:'Publicidad CPC', social:'Redes sociales', referral:'Referencia', email:'Email marketing', direct:'Acceso directo', none:'Acceso directo' };
    var mediumIcons = { organic:'fas fa-search', cpc:'fas fa-ad', social:'fas fa-share-alt', referral:'fas fa-link', email:'fas fa-envelope', direct:'fas fa-arrow-right', none:'fas fa-arrow-right' };

    function loadReferrers() {
        $('#referrers-list').html(spinner()); $('#referrers-pager').html('');
        $.get(ROUTES.topReferrers, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#referrers-list').html(emptyState('Sin datos')); return; }
                setPagerData('referrers', res.data);
                pagerRenders['referrers'] = function () {
                    var items = getPageItems('referrers').map(function(r, i) {
                        var bg    = 'bg-secondary-subtle';
                        var color = 'text-secondary';
                        var icon  = 'fas fa-arrow-right';
                        var label = (!r.url || r.url === '(direct)') ? 'Directo' : escHtml(r.url);
                        var isLast = i === getPageItems('referrers').length - 1;
                        return '<div class="d-flex align-items-center justify-content-between ' + (isLast ? '' : 'mb-4') + '">' +
                            '<div class="d-flex align-items-center gap-3">' +
                                '<div class="p-2 ' + bg + ' rounded-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;flex-shrink:0;">' +
                                    '<i class="' + icon + ' ' + color + '"></i>' +
                                '</div>' +
                                '<div>' +
                                    '<h6 class="mb-0 fw-semibold text-truncate" style="max-width:300px;">' + label + '</h6>' +
                                '</div>' +
                            '</div>' +
                            '<h6 class="mb-0 fw-semibold">' + fmt(r.views) + '</h6>' +
                        '</div>';
                    }).join('');
                    $('#referrers-list').html(items);
                    $('#referrers-pager').html(pagerNav('referrers'));
                };
                pagerRenders['referrers']();
            });
    }

    // ─── Search terms ──────────────────────────────────────────────────────
    function loadSearchTerms() {
        $('#search-terms-list').html(spinner()); $('#search-terms-pager').html('');
        $.get(ROUTES.searchTerms, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) {
                    $('#search-terms-list').html(emptyState('Sin datos de búsquedas'));
                    return;
                }
                setPagerData('searchTerms', res.data);
                pagerRenders['searchTerms'] = function () {
                    var rows = getPageItems('searchTerms').map(function(t, i) {
                        var badge = termBadges[i % termBadges.length];
                        var num   = (getPager('searchTerms').page - 1) * PER_PAGE + i + 1;
                        return '<tr>' +
                            '<td class="ps-0">' +
                                '<div class="d-flex align-items-center gap-2">' +
                                    '<span class="badge bg-secondary-subtle text-secondary">' + num + '</span>' +
                                    '<span class="fw-semibold">' + escHtml(t.term) + '</span>' +
                                '</div>' +
                            '</td>' +
                            '<td><span class="badge fw-semibold py-1 ' + badge + '">' + fmt(t.sessions) + '</span></td>' +
                            '<td><p class="text-muted">' + fmt(t.pageviews) + '</p></td>' +
                            '<td>' +
                                '<div class="d-flex align-items-center gap-2">' +
                                    '<div class="progress progress-thin flex-fill" style="min-width:50px;">' +
                                        '<div class="progress-bar bg-info" style="width:' + t.percentage + '%;"></div>' +
                                    '</div>' +
                                    '<p class="text-muted">' + t.percentage + '%</p>' +
                                '</div>' +
                            '</td>' +
                        '</tr>';
                    }).join('');
                    $('#search-terms-list').html(
                        '<table class="table align-middle text-nowrap mb-0">' +
                            '<thead><tr class="text-muted fw-semibold">' +
                                '<th scope="col" class="ps-0">Término</th>' +
                                '<th scope="col">Sesiones</th>' +
                                '<th scope="col">Vistas</th>' +
                                '<th scope="col">Participación</th>' +
                            '</tr></thead>' +
                            '<tbody class="border-top">' + rows + '</tbody>' +
                        '</table>'
                    );
                    $('#search-terms-pager').html(pagerNav('searchTerms'));
                };
                pagerRenders['searchTerms']();
            })
            .fail(function() { $('#search-terms-list').html(emptyState('Sin datos')); });
    }

    // ─── User flow ─────────────────────────────────────────────────────────
    function loadUserFlow() {
        $('#user-flow-list').html(spinner()); $('#user-flow-pager').html('');
        $.get(ROUTES.userFlow, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) {
                    $('#user-flow-list').html(emptyState('Sin datos de flujo'));
                    return;
                }
                setPagerData('userFlow', res.data);
                pagerRenders['userFlow'] = function () {
                    var rows = getPageItems('userFlow').map(function(r, i) {
                        var badge = termBadges[i % termBadges.length];
                        return '<tr>' +
                            '<td class="ps-0 text-truncate" style="max-width:180px;"><div class="small text-truncate">' + escHtml(r.landing) + '</div></td>' +
                            '<td class="text-truncate" style="max-width:180px;"><div class="small text-truncate">' + escHtml(r.exit) + '</div></td>' +
                            '<td><span class="badge fw-semibold py-1 ' + badge + '">' + fmt(r.sessions) + '</span></td>' +
                            '<td><p class="text-muted">' + r.bounce_rate + '%</p></td>' +
                        '</tr>';
                    }).join('');
                    $('#user-flow-list').html(
                        '<table class="table align-middle text-nowrap mb-0">' +
                            '<thead><tr class="text-muted fw-semibold">' +
                                '<th scope="col" class="ps-0">Entrada</th>' +
                                '<th scope="col">Salida</th>' +
                                '<th scope="col">Sesiones</th>' +
                                '<th scope="col">Rebote</th>' +
                            '</tr></thead>' +
                            '<tbody class="border-top">' + rows + '</tbody>' +
                        '</table>'
                    );
                    $('#user-flow-pager').html(pagerNav('userFlow'));
                };
                pagerRenders['userFlow']();
            })
            .fail(function() { $('#user-flow-list').html(emptyState('Sin datos')); });
    }

    // ─── Export ────────────────────────────────────────────────────────────
    function convertToCSV(data) {
        var rows = Array.isArray(data) ? data : (data.rows || data.chart_data || data.data || []);
        if (!rows.length) return '';
        var headers = Object.keys(rows[0]);
        return [
            headers.join(','),
        ].concat(rows.map(function(row) {
            return headers.map(function(h) {
                var val = row[h] != null ? row[h] : '';
                var str = String(val).replace(/"/g, '""');
                return (str.indexOf(',') >= 0 || str.indexOf('"') >= 0 || str.indexOf('\n') >= 0) ? '"' + str + '"' : str;
            }).join(',');
        })).join('\r\n');
    }

    window.exportData = function (type, format) {
        format = format || 'json';
        var data = exportCache[type];
        if (!data) { alert('Primero carga los datos del dashboard.'); return; }

        var blob, filename;
        var today = new Date().toISOString().slice(0, 10);
        if (format === 'csv') {
            blob     = new Blob([convertToCSV(data)], { type: 'text/csv;charset=utf-8;' });
            filename = 'analytics_' + type + '_' + selectedRange + '_' + today + '.csv';
        } else {
            blob     = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            filename = 'analytics_' + type + '_' + selectedRange + '_' + today + '.json';
        }

        var url = URL.createObjectURL(blob);
        var a   = document.createElement('a');
        a.href = url; a.download = filename; a.click();
        URL.revokeObjectURL(url);
    };

    // ─── Load all ──────────────────────────────────────────────────────────
    function loadAll() {
        currentTotals = null;
        $('#kpi-sessions,#kpi-users,#kpi-pageviews,#kpi-bounce,#kpi-new-users,#kpi-duration')
            .html('<span class="spinner-border spinner-border-sm text-muted"></span>');
        $('#cmp-sessions,#cmp-users,#cmp-pageviews,#cmp-bounce').html('');
        $('#export-period-label').text($('#rangePills .nav-link.active').text().trim());

        loadOverview();
        loadSessionMetrics();
        loadBrowsers();
        loadDevices();
        loadOS();
        loadCountries();
        loadTrafficSources();
        loadTopPages();
        loadLandingPages();
        loadExitPages();
        loadReferrers();
        loadChannelTrend();
        loadHourlyHeatmap();
        loadSearchTerms();
        loadUserFlow();
    }

    // ─── Events ────────────────────────────────────────────────────────────
    $('#rangePills').on('click', '.nav-link', function (e) {
        e.preventDefault();
        selectedRange = $(this).data('range');
        $('#rangePills .nav-link').removeClass('active').addClass('text-muted');
        $(this).addClass('active').removeClass('text-muted');
        loadAll();
    });

    $('#refreshBtn').on('click', loadAll);

    loadRealtime();
    setInterval(loadRealtime, 30000);

    $(document).ready(function () {
        initMap();
        loadAll();
    });

})();
</script>
@endpush
