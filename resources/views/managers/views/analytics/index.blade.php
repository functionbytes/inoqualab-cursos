@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Analytics'])

    <div class="widget-content searchable-container list">

        {{-- Filters bar --}}
        <div class="card card-body mb-4 border-0 shadow-sm">
            <div class="row align-items-center g-3">

                <div class="col-md-auto">
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

                <div class="col-md">
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

                <div class="col-md-auto">
                    <a href="javascript:void(0)" id="refreshBtn"
                       class="d-flex align-items-center justify-content-center rounded-circle text-muted"
                       style="width:32px;height:32px;background:#f5f6f8;" title="Actualizar">
                        <i class="fas fa-rotate-right"></i>
                    </a>
                </div>

            </div>
        </div>

        {{-- KPI cards --}}
        <div class="row mb-4 g-3">

            <div class="col-lg-4 col-md-6">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Sesiones</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-sessions">
                                    <span class="spinner-border spinner-border-sm text-muted"></span>
                                </h4>
                                <div id="cmp-sessions"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-sessions"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Usuarios</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-users">
                                    <span class="spinner-border spinner-border-sm text-muted"></span>
                                </h4>
                                <div id="cmp-users"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-users"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Vistas de página</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-pageviews">
                                    <span class="spinner-border spinner-border-sm text-muted"></span>
                                </h4>
                                <div id="cmp-pageviews"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-pageviews"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Tasa de rebote</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-bounce">
                                    <span class="spinner-border spinner-border-sm text-muted"></span>
                                </h4>
                                <div id="cmp-bounce"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-bounce"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Usuarios nuevos</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-new-users">
                                    <span class="spinner-border spinner-border-sm text-muted"></span>
                                </h4>
                                <p class="fs-3 mb-0 text-muted">registrados</p>
                            </div>
                            <div class="col-4 d-flex justify-content-end">
                                <span class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center"
                                      style="width:44px;height:44px;">
                                    <i class="fas fa-user-plus text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Duración media</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-duration">
                                    <span class="spinner-border spinner-border-sm text-muted"></span>
                                </h4>
                                <p class="fs-3 mb-0 text-muted">por sesión</p>
                            </div>
                            <div class="col-4 d-flex justify-content-end">
                                <span class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center"
                                      style="width:44px;height:44px;">
                                    <i class="fas fa-clock text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Daily chart --}}
        <div class="row mb-4 g-3">
            <div class="col-12">
                <div class="card w-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Sesiones y vistas de página</h4>
                        <p class="card-subtitle mt-1">Tendencia diaria del período seleccionado</p>
                    </div>
                    <div class="card-body">
                        <div id="daily-chart" style="height:300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Devices + Browsers --}}
        <div class="row mb-4 g-3">
            <div class="col-lg-6">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Dispositivos</h4>
                        <p class="card-subtitle mt-1">Por sesiones</p>
                    </div>
                    <div class="card-body">
                        <div id="devices-chart" class="mb-4" style="height:200px;"></div>
                        <hr>
                        <div id="devices-list"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Navegadores</h4>
                        <p class="card-subtitle mt-1">Por vistas de página</p>
                    </div>
                    <div class="card-body">
                        <div id="browser-chart" class="mb-4" style="height:200px;"></div>
                        <hr>
                        <div id="browser-list"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Channels + Countries --}}
        <div class="row mb-4 g-3">
            <div class="col-lg-6">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Canales de tráfico</h4>
                        <p class="card-subtitle mt-1">Origen del tráfico por sesiones</p>
                    </div>
                    <div class="card-body">
                        <div id="channels-list"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card w-100 h-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Países</h4>
                        <p class="card-subtitle mt-1">Distribución geográfica de vistas</p>
                    </div>
                    <div class="card-body p-0">
                        <div id="countries-list" style="max-height:340px;overflow-y:auto;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pages + Referrers --}}
        <div class="row g-3">
            <div class="col-12">
                <div class="card w-100">
                    <div class="card-header d-md-flex align-items-center">
                        <div>
                            <h4 class="card-title fw-semibold mb-0">Páginas</h4>
                            <p class="card-subtitle mt-1">Análisis de páginas visitadas y fuentes</p>
                        </div>
                        <div class="ms-auto mt-3 mt-md-0">
                            <ul class="nav nav-tabs border-0" id="pagesTabs">
                                <li class="nav-item">
                                    <a class="nav-link rounded active" data-bs-toggle="tab" href="#tab-top-pages">Más visitadas</a>
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
                            <div class="tab-pane fade" id="tab-referrers">
                                <div id="referrers-list"></div>
                                <div id="referrers-pager" class="px-1 pt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('css')
<style>
    .analytics-country-item {
        padding: 10px 16px;
        border-bottom: 1px solid #f5f6f8;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .analytics-country-item:last-child { border-bottom: none; }
    .progress-thin { height: 5px; }
    .cmp-up   { color: #198754; }
    .cmp-down { color: #dc3545; }
    #rangePills .nav-link.active { background: #008bce; color: #fff !important; }
    #rangePills .nav-link:hover:not(.active) { background: #f0f7ff; color: #008bce; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js"></script>
<script>
(function () {
    'use strict';

    let selectedRange   = '{{ $range }}';
    let dailyChart      = null;
    let browserChart    = null;
    let devicesChart    = null;
    let currentTotals   = null;
    const _sparks       = {};

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
    };

    const PRIMARY  = '#008bce';
    const PRIMARY2 = '#0065a0';
    const DARK     = '#333333';
    const PALETTE  = [PRIMARY, DARK, PRIMARY2, '#555', '#7ab8d9', '#aaa'];

    // ─── helpers ──────────────────────────────────────────────────────────
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

    function cmpBadge(current, previous, invert = false) {
        if (!previous || previous === 0) return '';
        const pct  = ((current - previous) / previous * 100).toFixed(1);
        const up   = invert ? parseFloat(pct) < 0 : parseFloat(pct) > 0;
        const icon = up ? 'fa-arrow-up' : 'fa-arrow-down';
        const cls  = up ? 'cmp-up' : 'cmp-down';
        const sign = parseFloat(pct) > 0 ? '+' : '';
        return `<div class="d-flex align-items-center gap-1">
            <i class="fas ${icon} ${cls}" style="font-size:0.65rem;"></i>
            <span class="${cls} fw-semibold" style="font-size:0.8rem;">${sign}${Math.abs(pct)}%</span>
            <span class="text-muted" style="font-size:0.75rem;">vs anterior</span>
        </div>`;
    }

    // ─── period pills ─────────────────────────────────────────────────────
    $('#rangePills').on('click', '.nav-link', function (e) {
        e.preventDefault();
        selectedRange = $(this).data('range');
        $('#rangePills .nav-link').removeClass('active').addClass('text-muted');
        $(this).addClass('active').removeClass('text-muted');
        loadAll();
    });

    $('#refreshBtn').on('click', loadAll);

    // ─── realtime ─────────────────────────────────────────────────────────
    function loadRealtime() {
        $.get(ROUTES.realtime)
            .done(function (res) {
                if (!res.success) return;
                $('#realtime-count').text(fmt(res.data.active_users));
                const now = new Date();
                $('#realtime-updated').text('Act. ' + now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
            })
            .fail(function () { $('#realtime-count').text('—'); });
    }

    // ─── overview ─────────────────────────────────────────────────────────
    function loadOverview() {
        ['sessions', 'users', 'pageviews', 'bounce'].forEach(k => {
            $('#kpi-' + k).html('<span class="spinner-border spinner-border-sm text-muted"></span>');
            $('#cmp-' + k).html('');
        });

        $.get(ROUTES.overview, { range: selectedRange })
            .done(function (res) {
                if (!res.success) { setKpiError(); return; }
                const t = res.data.totals;
                currentTotals = t;

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
        ['sessions', 'users', 'pageviews', 'bounce'].forEach(k => $('#kpi-' + k).text('—'));
    }

    function loadComparison() {
        if (!currentTotals) return;
        $.get(ROUTES.comparison, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !currentTotals) return;
                const p = res.data;
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

    // ─── daily chart ──────────────────────────────────────────────────────
    function renderDailyChart(chartData) {
        if (!chartData || !chartData.length) {
            $('#daily-chart').html(emptyState('Sin datos para el período'));
            return;
        }

        const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        const fmtDate = d => {
            if (!d || d.length !== 8) return d;
            return months[parseInt(d.slice(4, 6)) - 1] + ' ' + parseInt(d.slice(6, 8));
        };

        const dates    = chartData.map(r => fmtDate(r.date || ''));
        const sessions = chartData.map(r => parseInt(r.sessions || 0));
        const views    = chartData.map(r => parseInt(r.screenPageViews || 0));

        if (dailyChart) { dailyChart.destroy(); dailyChart = null; }
        $('#daily-chart').html('');

        dailyChart = new ApexCharts(document.querySelector('#daily-chart'), {
            series: [
                { name: 'Sesiones',         data: sessions },
                { name: 'Vistas de página', data: views },
            ],
            chart: {
                type: 'area', height: 295,
                toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'inherit',
            },
            colors: [PRIMARY, DARK],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.15, opacityTo: 0.02, stops: [0, 100] } },
            xaxis: {
                categories: dates,
                labels: { rotate: -30, style: { fontSize: '11px', colors: '#adb5bd' } },
                axisBorder: { show: false }, axisTicks: { show: false },
            },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#adb5bd' }, formatter: v => Math.round(v) } },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
            tooltip: { theme: 'light', shared: true, intersect: false },
            legend: { position: 'top', horizontalAlign: 'right', fontFamily: 'inherit' },
            markers: { size: 0 },
        });
        dailyChart.render();
    }

    // ─── sparklines ───────────────────────────────────────────────────────
    function renderSparklines(chartData) {
        if (!chartData || !chartData.length) return;

        const mkCfg = (data, color, type) => ({
            series: [{ data }],
            chart: {
                type, height: 70, width: 80,
                sparkline: { enabled: true }, animations: { enabled: false }, fontFamily: 'inherit',
            },
            colors: [color],
            stroke: { curve: 'smooth', width: 2 },
            fill: type === 'area'
                ? { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } }
                : { type: 'solid' },
            tooltip: { fixed: { enabled: false }, x: { show: false }, y: { title: { formatter: () => '' } } },
            plotOptions: { bar: { borderRadius: 2, columnWidth: '60%' } },
        });

        const defs = {
            '#spark-sessions':  { data: chartData.map(r => r.sessions  || 0),                                         color: PRIMARY,  type: 'area' },
            '#spark-users':     { data: chartData.map(r => r.totalUsers || 0),                                         color: DARK,     type: 'bar'  },
            '#spark-pageviews': { data: chartData.map(r => r.screenPageViews || 0),                                    color: PRIMARY2, type: 'bar'  },
            '#spark-bounce':    { data: chartData.map(r => parseFloat(((r.bounceRate || 0) * 100).toFixed(1))),        color: '#555',   type: 'area' },
        };

        Object.entries(defs).forEach(([sel, cfg]) => {
            const el = document.querySelector(sel);
            if (!el) return;
            if (_sparks[sel]) { _sparks[sel].destroy(); }
            _sparks[sel] = new ApexCharts(el, mkCfg(cfg.data, cfg.color, cfg.type));
            _sparks[sel].render();
        });
    }

    // ─── browsers ─────────────────────────────────────────────────────────
    const browserIconMap = {
        chrome: 'fab fa-chrome', safari: 'fab fa-safari',
        firefox: 'fab fa-firefox-browser', edge: 'fab fa-edge',
        opera: 'fab fa-opera', samsung: 'fas fa-mobile-alt',
    };

    function loadBrowsers() {
        $('#browser-chart').html(spinner()); $('#browser-list').html('');

        $.get(ROUTES.browsers, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#browser-chart').html(emptyState('Sin datos')); return; }
                const data = res.data;

                if (browserChart) { browserChart.destroy(); browserChart = null; }
                $('#browser-chart').html('');

                browserChart = new ApexCharts(document.querySelector('#browser-chart'), {
                    series: data.map(b => b.sessions),
                    labels: data.map(b => b.name),
                    chart: { type: 'donut', height: 200, fontFamily: 'inherit' },
                    colors: PALETTE.slice(0, data.length),
                    legend: { show: false },
                    dataLabels: { enabled: false },
                    tooltip: { y: { formatter: v => fmt(v) } },
                    plotOptions: { pie: { donut: { size: '72%' } } },
                });
                browserChart.render();

                $('#browser-list').html(data.slice(0, 5).map((b, i) => {
                    const key  = b.name.toLowerCase().replace(/\s+/g, '');
                    const icon = browserIconMap[key] || 'fas fa-globe';
                    return `<div class="d-flex align-items-center justify-content-between ${i < data.slice(0,5).length - 1 ? 'mb-3' : ''}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle"
                                 style="width:36px;height:36px;flex-shrink:0;">
                                <i class="${icon} text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">${escHtml(b.name)}</h6>
                                <p class="fs-3 mb-0 text-muted">${fmt(b.sessions)} vistas</p>
                            </div>
                        </div>
                        <span class="fw-bold" style="font-size:0.85rem;">${b.percentage}%</span>
                    </div>`;
                }).join(''));
            })
            .fail(() => $('#browser-chart').html(emptyState('Error al cargar datos')));
    }

    // ─── devices ──────────────────────────────────────────────────────────
    const deviceIcons  = { desktop: 'fas fa-desktop', mobile: 'fas fa-mobile-alt', tablet: 'fas fa-tablet-alt' };
    const deviceLabels = { desktop: 'Escritorio', mobile: 'Móvil', tablet: 'Tablet' };

    function loadDevices() {
        $('#devices-chart').html(spinner()); $('#devices-list').html('');

        $.get(ROUTES.devices, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#devices-chart').html(emptyState('Sin datos')); return; }
                const data = res.data;

                if (devicesChart) { devicesChart.destroy(); devicesChart = null; }
                $('#devices-chart').html('');

                devicesChart = new ApexCharts(document.querySelector('#devices-chart'), {
                    series: data.map(d => d.sessions),
                    labels: data.map(d => d.device),
                    chart: { type: 'donut', height: 200, fontFamily: 'inherit' },
                    colors: PALETTE.slice(0, data.length),
                    legend: { show: false },
                    dataLabels: { enabled: false },
                    tooltip: { y: { formatter: v => fmt(v) } },
                    plotOptions: { pie: { donut: { size: '72%' } } },
                });
                devicesChart.render();

                $('#devices-list').html(data.map((d, i) => {
                    const key   = d.device.toLowerCase();
                    const icon  = deviceIcons[key]  || 'fas fa-question-circle';
                    const label = deviceLabels[key] || escHtml(d.device);
                    return `<div class="d-flex align-items-center justify-content-between ${i < data.length - 1 ? 'mb-3' : ''}">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle"
                                 style="width:36px;height:36px;flex-shrink:0;">
                                <i class="${icon} text-primary"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold">${label}</h6>
                                <p class="fs-3 mb-0 text-muted">${fmt(d.sessions)} sesiones</p>
                            </div>
                        </div>
                        <span class="fw-bold" style="font-size:0.85rem;">${d.percentage}%</span>
                    </div>`;
                }).join(''));
            })
            .fail(() => $('#devices-chart').html(emptyState('Error al cargar datos')));
    }

    // ─── channels ─────────────────────────────────────────────────────────
    const channelIcons = {
        'organic search': 'fas fa-search',
        'direct':         'fas fa-link',
        'referral':       'fas fa-external-link-alt',
        'social':         'fas fa-share-alt',
        'email':          'fas fa-envelope',
        'paid search':    'fas fa-ad',
        'display':        'fas fa-image',
        'other':          'fas fa-ellipsis-h',
    };

    function loadChannels() {
        $('#channels-list').html(spinner());

        $.get(ROUTES.channels, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#channels-list').html(emptyState('Sin datos')); return; }
                const data = res.data;

                $('#channels-list').html(data.slice(0, 8).map((c, i) => {
                    const key   = (c.channel || '').toLowerCase();
                    const icon  = channelIcons[key] || 'fas fa-globe';
                    const color = PALETTE[i % PALETTE.length];
                    return `<div class="${i < data.slice(0,8).length - 1 ? 'mb-3' : ''}">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <i class="${icon}" style="color:${color};width:16px;text-align:center;font-size:0.8rem;"></i>
                                <span class="fw-semibold" style="font-size:0.85rem;">${escHtml(c.channel)}</span>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-dark" style="font-size:0.85rem;">${fmt(c.sessions)}</span>
                                <span class="text-muted ms-1" style="font-size:0.75rem;">${c.percentage}%</span>
                            </div>
                        </div>
                        <div class="progress rounded-pill" style="height:5px;">
                            <div class="progress-bar rounded-pill" style="width:${c.percentage}%;background:${color};"></div>
                        </div>
                    </div>`;
                }).join(''));
            })
            .fail(() => $('#channels-list').html(emptyState('Error al cargar datos')));
    }

    // ─── countries ────────────────────────────────────────────────────────
    const FLAGS = {
        'Afghanistan':'🇦🇫','Argentina':'🇦🇷','Australia':'🇦🇺','Austria':'🇦🇹','Belgium':'🇧🇪',
        'Bolivia':'🇧🇴','Brazil':'🇧🇷','Canada':'🇨🇦','Chile':'🇨🇱','China':'🇨🇳',
        'Colombia':'🇨🇴','Costa Rica':'🇨🇷','Cuba':'🇨🇺','Denmark':'🇩🇰','Dominican Republic':'🇩🇴',
        'Ecuador':'🇪🇨','Egypt':'🇪🇬','El Salvador':'🇸🇻','France':'🇫🇷','Germany':'🇩🇪',
        'Guatemala':'🇬🇹','Honduras':'🇭🇳','India':'🇮🇳','Indonesia':'🇮🇩','Ireland':'🇮🇪',
        'Italy':'🇮🇹','Japan':'🇯🇵','Malaysia':'🇲🇾','Mexico':'🇲🇽','Morocco':'🇲🇦',
        'Netherlands':'🇳🇱','New Zealand':'🇳🇿','Nicaragua':'🇳🇮','Nigeria':'🇳🇬',
        'Norway':'🇳🇴','Pakistan':'🇵🇰','Panama':'🇵🇦','Paraguay':'🇵🇾','Peru':'🇵🇪',
        'Philippines':'🇵🇭','Poland':'🇵🇱','Portugal':'🇵🇹','Romania':'🇷🇴','Russia':'🇷🇺',
        'Saudi Arabia':'🇸🇦','Spain':'🇪🇸','Sweden':'🇸🇪','Switzerland':'🇨🇭',
        'Turkey':'🇹🇷','Ukraine':'🇺🇦','United Arab Emirates':'🇦🇪','United Kingdom':'🇬🇧',
        'United States':'🇺🇸','Uruguay':'🇺🇾','Venezuela':'🇻🇪','Vietnam':'🇻🇳',
    };

    function loadCountries() {
        $('#countries-list').html(spinner());

        $.get(ROUTES.countries, { range: selectedRange })
            .done(function (res) {
                if (!res.success || !res.data.length) { $('#countries-list').html(emptyState('Sin datos')); return; }
                const data = res.data;

                const rankColor = i => i === 0 ? PRIMARY : i === 1 ? PRIMARY2 : i === 2 ? '#7ab8d9' : '#ccc';

                $('#countries-list').html(data.slice(0, 15).map((c, i) => `
                    <div class="analytics-country-item">
                        <span class="fw-bold flex-shrink-0 text-end"
                              style="font-size:0.75rem;width:18px;color:${rankColor(i)};">${i + 1}</span>
                        <span style="font-size:1.2rem;">${FLAGS[c.country] || '🌐'}</span>
                        <div style="min-width:0;flex:1;">
                            <div class="fw-semibold text-truncate mb-1" style="font-size:0.82rem;">${escHtml(c.country)}</div>
                            <div class="progress rounded-pill" style="height:5px;">
                                <div class="progress-bar rounded-pill"
                                     style="width:${c.percentage}%;background:${PRIMARY};"></div>
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="fw-bold lh-1" style="font-size:0.9rem;color:${PRIMARY};">${fmt(c.sessions)}</div>
                            <div class="text-muted lh-1 mt-1" style="font-size:0.7rem;">${c.percentage}%</div>
                        </div>
                    </div>`).join(''));
            })
            .fail(() => $('#countries-list').html(emptyState('Error al cargar datos')));
    }

    // ─── pages ────────────────────────────────────────────────────────────
    let pagesData   = [], pagesPage = 0, pagesPerPage = 10;

    function loadTopPages() {
        $('#pages-list').html(spinner()); $('#pages-pager').html('');

        $.get(ROUTES.topPages, { range: selectedRange })
            .done(function (res) {
                if (!res.success) { $('#pages-list').html(emptyState('Sin datos')); return; }
                pagesData = res.data;
                pagesPage = 0;
                renderPagesTable();
            })
            .fail(() => $('#pages-list').html(emptyState('Error al cargar datos')));
    }

    function renderPagesTable() {
        const slice = pagesData.slice(pagesPage * pagesPerPage, (pagesPage + 1) * pagesPerPage);
        const total = Math.ceil(pagesData.length / pagesPerPage);

        if (!slice.length) { $('#pages-list').html(emptyState('Sin datos disponibles')); return; }

        $('#pages-list').html(`
            <table class="table search-table align-middle">
                <thead class="header-item">
                    <tr><th>Página</th><th class="text-end">Vistas</th></tr>
                </thead>
                <tbody>
                    ${slice.map(p => `
                    <tr>
                        <td style="max-width:500px;">
                            <div class="fw-semibold text-truncate">${escHtml(p.title)}</div>
                            <small class="text-muted text-truncate d-block" style="max-width:460px;">${escHtml(p.url)}</small>
                        </td>
                        <td class="text-end fw-bold">${fmt(p.views)}</td>
                    </tr>`).join('')}
                </tbody>
            </table>`);

        renderPager('#pages-pager', total, pagesPage, 'page-btn');
    }

    $(document).on('click', '#pages-pager .page-btn', function () {
        pagesPage = parseInt($(this).data('page'));
        renderPagesTable();
    });

    // ─── referrers ────────────────────────────────────────────────────────
    let referrersData = [], refPage = 0, refPerPage = 10;

    function loadReferrers() {
        $('#referrers-list').html(spinner()); $('#referrers-pager').html('');

        $.get(ROUTES.topReferrers, { range: selectedRange })
            .done(function (res) {
                if (!res.success) { $('#referrers-list').html(emptyState('Sin datos')); return; }
                referrersData = res.data;
                refPage = 0;
                renderReferrersTable();
            })
            .fail(() => $('#referrers-list').html(emptyState('Error al cargar datos')));
    }

    function renderReferrersTable() {
        const slice = referrersData.slice(refPage * refPerPage, (refPage + 1) * refPerPage);
        const total = Math.ceil(referrersData.length / refPerPage);

        if (!slice.length) { $('#referrers-list').html(emptyState('Sin datos disponibles')); return; }

        $('#referrers-list').html(`
            <table class="table search-table align-middle">
                <thead class="header-item">
                    <tr><th>Fuente</th><th class="text-end">Vistas</th></tr>
                </thead>
                <tbody>
                    ${slice.map(r => `
                    <tr>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width:460px;">${escHtml(r.url)}</span>
                        </td>
                        <td class="text-end fw-bold">${fmt(r.views)}</td>
                    </tr>`).join('')}
                </tbody>
            </table>`);

        renderPager('#referrers-pager', total, refPage, 'ref-btn');
    }

    $(document).on('click', '#referrers-pager .ref-btn', function () {
        refPage = parseInt($(this).data('page'));
        renderReferrersTable();
    });

    // ─── pager helper ─────────────────────────────────────────────────────
    function renderPager(selector, total, current, cls) {
        if (total <= 1) { $(selector).html(''); return; }
        let btns = '';
        for (let i = 0; i < total; i++) {
            btns += `<button class="btn btn-sm ${i === current ? 'btn-primary' : 'btn-outline-secondary'} me-1 ${cls}"
                             data-page="${i}">${i + 1}</button>`;
        }
        $(selector).html('<div class="d-flex align-items-center gap-1">' + btns + '</div>');
    }

    // ─── load all ─────────────────────────────────────────────────────────
    function loadAll() {
        loadOverview();
        loadSessionMetrics();
        loadBrowsers();
        loadDevices();
        loadChannels();
        loadCountries();
        loadTopPages();
        loadReferrers();
        loadRealtime();
    }

    $(function () {
        loadAll();
        setInterval(loadRealtime, 30000);
    });

})();
</script>
@endpush
