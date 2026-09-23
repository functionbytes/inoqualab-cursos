@extends('layouts.managers')

@section('title', 'Dashboard SEO')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Dashboard SEO'])
@endsection

@section('content')


    @php
        $trendValues = array_values($trend);
        $trendLabels = array_keys($trend);
        $hasTrend    = !empty($trend) && array_sum($trendValues) > 0;
        $gradeTotal  = $gradeDistribution
            ? (int)($gradeDistribution->A + $gradeDistribution->B + $gradeDistribution->C + $gradeDistribution->D + $gradeDistribution->F)
            : 0;

        $dashboardConfig = [
            'trendValues' => $trendValues,
            'trendLabels' => $trendLabels,
            'hasTrend' => $hasTrend,
        ];
    @endphp

    <div class="widget-content"
         id="seo-dashboard"
         data-config='@json($dashboardConfig)'>

        {{-- ── Fila 1 de KPIs ──────────────────────────────────────────────── --}}
        <div class="row g-3 mb-3">

            {{-- Score promedio SEO --}}
            <div class="col-lg-4 col-md-6">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Score promedio SEO</h5>
                                <h4 class="fw-semibold mb-2">
                                    {{ $metaStats['avg_score'] > 0 ? $metaStats['avg_score'] : '—' }}
                                </h4>
                                <p class="fs-3 mb-0 text-muted">
                                    {{ number_format($metaStats['with_score']) }} páginas auditadas
                                </p>
                            </div>
                            <div class="col-4">
                                <div class="d-flex justify-content-center">
                                    @if($hasTrend)
                                        <div id="spark-score"></div>
                                    @else
                                        <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center brand-box-dark">
                                            <i class="fas fa-star fs-4"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total metas SEO --}}
            <div class="col-lg-4 col-md-6">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Total metas SEO</h5>
                                <h4 class="fw-semibold mb-2">{{ number_format($metaStats['total']) }}</h4>
                                <p class="fs-3 mb-0 text-muted">
                                    <span class="badge bg-primary-subtle text-primary me-1">
                                        {{ number_format($metaStats['indexable']) }} indexables
                                    </span>
                                    <span class="badge brand-badge-noindex">
                                        {{ number_format($metaStats['noindex']) }} noindex
                                    </span>
                                </p>
                            </div>
                            <div class="col-4">
                                <div class="d-flex justify-content-center">
                                    @if($hasTrend)
                                        <div id="spark-total"></div>
                                    @else
                                        <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle">
                                            <i class="fas fa-tags text-primary fs-4"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Redirects activos --}}
            <div class="col-lg-4 col-md-6">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Redirects activos</h5>
                                <h4 class="fw-semibold mb-2">{{ number_format($redirectStats['active']) }}</h4>
                                <p class="fs-3 mb-0 text-muted">
                                    {{ number_format($redirectStats['total_hits']) }} hits totales
                                </p>
                            </div>
                            <div class="col-4">
                                <div class="d-flex justify-content-center">
                                    @if($hasTrend)
                                        <div id="spark-redirects"></div>
                                    @else
                                        <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center brand-box-dark">
                                            <i class="fas fa-route fs-4"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Fila 2 de KPIs ──────────────────────────────────────────────── --}}
        <div class="row g-3 mb-3">

            {{-- Sin OG image --}}
            <div class="col-lg-4 col-md-6">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Sin OG image</h5>
                                <h4 class="fw-semibold mb-2">{{ number_format($metaStats['missing_og_image']) }}</h4>
                                <p class="fs-3 mb-0 text-muted">Páginas sin imagen social</p>
                            </div>
                            <div class="col-4">
                                <div class="d-flex justify-content-center">
                                    @if($hasTrend)
                                        <div id="spark-og"></div>
                                    @else
                                        <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center brand-box-red">
                                            <i class="fas fa-image fs-4"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Objetivo de puntuación --}}
            <div class="col-lg-4 col-md-6">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Objetivo de puntuación</h5>
                                <h4 class="fw-semibold mb-2">{{ $goalPercent }}%</h4>
                                <p class="fs-3 mb-0 text-muted">
                                    {{ number_format($meetingGoal) }} de {{ number_format($metaStats['total']) }}
                                    ≥ {{ $scoreGoal }}
                                </p>
                            </div>
                            <div class="col-4">
                                <div class="d-flex justify-content-center">
                                    <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle">
                                        <i class="fas fa-bullseye text-primary fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Errores 404 --}}
            <div class="col-lg-4 col-md-6">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Errores 404</h5>
                                <h4 class="fw-semibold mb-2">{{ number_format($total404) }}</h4>
                                <p class="fs-3 mb-0 text-muted">URLs no encontradas</p>
                            </div>
                            <div class="col-4">
                                <div class="d-flex justify-content-center">
                                    <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center brand-box-red">
                                        <i class="fas fa-exclamation-triangle fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Tendencia últimos 7 días ─────────────────────────────────────── --}}
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="card w-100">
                    <div class="card-header border-bottom">
                        <h6 class="mb-0 fw-bold">Tendencia últimos 7 días</h6>
                    </div>
                    <div class="card-body">
                        @if($hasTrend)
                            <div id="trendChart" class="seo-chart-260"></div>
                        @else
                            <div class="text-center py-5">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 seo-empty-icon-circle">
                                    <i class="fas fa-chart-area text-secondary fs-5"></i>
                                </div>
                                <p class="mb-1 fw-semibold text-muted">Sin datos de tendencia</p>
                                <p class="text-muted">No hay metas actualizadas en los últimos 7 días</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Distribución + Peor score ────────────────────────────────────── --}}
        <div class="row g-3 mb-3">

            {{-- Distribución por grado --}}
            <div class="col-lg-6">
                <div class="card w-100 h-100">
                    <div class="card-header border-bottom d-flex align-items-start justify-content-between">
                        <div>
                            <h6 class="mb-1 fw-bold">Distribución por grado</h6>
                            <p class="text-muted small mb-0">{{ number_format($gradeTotal) }} páginas evaluadas</p>
                        </div>
                        @if($gradeTotal > 0)
                            <div class="text-muted small text-end">
                                Score prom.<br>
                                <span class="seo-grade-score-inline">{{ $metaStats['avg_score'] > 0 ? $metaStats['avg_score'] : '—' }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($gradeTotal > 0)
                            @php
                                // Mismos colores/cortes que metas/index y reporte (ver
                                // public/managers/css/includes/seo-badges.css).
                                $gradeMeta = [
                                    'A' => ['range' => '90+',   'class' => 'seo-score--a'],
                                    'B' => ['range' => '75–89', 'class' => 'seo-score--b'],
                                    'C' => ['range' => '60–74', 'class' => 'seo-score--c'],
                                    'D' => ['range' => '40–59', 'class' => 'seo-score--d'],
                                    'F' => ['range' => '<40',   'class' => 'seo-score--f'],
                                ];
                            @endphp
                            <div class="d-flex flex-column gap-3">
                                @foreach($gradeMeta as $grade => $meta)
                                    @php
                                        $count = (int) ($gradeDistribution->{$grade} ?? 0);
                                        $percent = round(($count / $gradeTotal) * 100);
                                    @endphp
                                    <div>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="fw-semibold seo-grade-bar-label">{{ $grade }} · {{ $meta['range'] }}</span>
                                            <span class="text-muted">{{ number_format($count) }} {{ Str::plural('página', $count) }} · {{ $percent }}%</span>
                                        </div>
                                        <div class="progress seo-grade-bar-track">
                                            <div class="progress-bar {{ $meta['class'] }} grade-progress-bar"
                                                 role="progressbar"
                                                 data-width="{{ $percent }}"
                                                 aria-valuenow="{{ $percent }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 seo-empty-icon-circle">
                                    <i class="fas fa-chart-pie text-secondary fs-5"></i>
                                </div>
                                <p class="mb-1 fw-semibold text-muted">Sin datos de score</p>
                                <p class="text-muted">Ejecuta una auditoría para ver la distribución</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Páginas con peor score --}}
            <div class="col-lg-6">
                <div class="card w-100 h-100">
                    <div class="card-header border-bottom">
                        <h6 class="mb-0 fw-bold">Páginas con peor score</h6>
                    </div>
                    <div class="card-body">
                        @if($worstPages->isEmpty())
                            <div class="text-center py-5">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 seo-empty-icon-circle">
                                    <i class="fas fa-list text-secondary fs-5"></i>
                                </div>
                                <p class="mb-1 fw-semibold text-muted">Sin datos</p>
                                <p class="text-muted">No hay metas con score calculado</p>
                            </div>
                        @else
                            @php
                                // Mismos cortes y colores que metas/index y reporte (ver
                                // public/managers/css/includes/seo-badges.css). Bajo 70 se
                                // agrupa como "prioridad" (por debajo del corte B/C de 75,
                                // igual que el resto del dashboard).
                                $worstCritical = $worstPages->filter(fn ($page) => $page->seo_score < 70)->values();
                                $worstImprovable = $worstPages->filter(fn ($page) => $page->seo_score >= 70)->values();
                            @endphp

                            @if($worstCritical->isNotEmpty())
                                <div class="mb-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="seo-worst-dot seo-worst-dot--critical"></span>
                                        <span class="seo-worst-severity-label seo-worst-severity-label--critical">Prioridad — bajo 70</span>
                                    </div>
                                    <div id="seo-worst-pages">
                                        @foreach($worstCritical as $page)
                                            @include('managers.views.seo.dashboard._worst-page-row', ['page' => $page, 'critical' => true])
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($worstImprovable->isNotEmpty())
                                <div>
                                    @if($worstCritical->isNotEmpty())
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="seo-worst-dot seo-worst-dot--improvable"></span>
                                            <span class="seo-worst-severity-label">Mejorable — 70 a 89</span>
                                        </div>
                                    @endif
                                    <div>
                                        @foreach($worstImprovable as $page)
                                            @include('managers.views.seo.dashboard._worst-page-row', ['page' => $page, 'critical' => false])
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/includes/seo-badges.css') }}">
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/dashboard/index.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js"></script>
<script src="{{ asset('managers/js/views/seo/dashboard/index.js') }}"></script>
@endpush

