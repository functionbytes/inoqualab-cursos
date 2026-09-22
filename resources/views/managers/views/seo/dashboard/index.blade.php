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
            'gradeTotal' => $gradeTotal,
            'gradeDistribution' => $gradeDistribution,
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
                                    <span class="badge bg-success-subtle text-success me-1">
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
                                        <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center bg-info-subtle">
                                            <i class="fas fa-route text-info fs-4"></i>
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
                                    <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center bg-success-subtle">
                                        <i class="fas fa-bullseye text-success fs-4"></i>
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
                    <div class="card-body">
                        <h5 class="card-title fw-semibold mb-3">Tendencia últimos 7 días</h5>
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
                    <div class="card-body">
                        <h5 class="card-title fw-semibold mb-3">Distribución por grado</h5>
                        @if($gradeTotal > 0)
                            <div id="gradeDonut" class="seo-chart-260"></div>
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
                    <div class="card-body">
                        <h5 class="card-title fw-semibold mb-3">Páginas con peor score</h5>
                        @if($worstPages->isEmpty())
                            <div class="text-center py-5">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 seo-empty-icon-circle">
                                    <i class="fas fa-list text-secondary fs-5"></i>
                                </div>
                                <p class="mb-1 fw-semibold text-muted">Sin datos</p>
                                <p class="text-muted">No hay metas con score calculado</p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($worstPages as $page)
                                    @php
                                        // Mismos cortes y colores que metas/index y reporte (ver
                                        // public/managers/css/includes/seo-badges.css).
                                        $sc = $page->seo_score;
                                        $scoreBg = match(true) {
                                            $sc >= 90 => 'seo-score--a',
                                            $sc >= 75 => 'seo-score--b',
                                            $sc >= 60 => 'seo-score--c',
                                            $sc >= 40 => 'seo-score--d',
                                            default   => 'seo-score--f',
                                        };
                                        $typeLabel = match(true) {
                                            str_contains($page->seoable_type ?? '', 'Course') => 'Curso',
                                            str_contains($page->seoable_type ?? '', 'Blog')   => 'Blog',
                                            str_contains($page->seoable_type ?? '', 'Bundle') => 'Bundle',
                                            default => class_basename($page->seoable_type ?? ''),
                                        };
                                    @endphp
                                    <div class="list-group-item px-0 py-2 border-0 border-bottom d-flex align-items-center gap-2">
                                        <span class="badge {{ $scoreBg }} flex-shrink-0 seo-score-badge">
                                            {{ $sc }}
                                        </span>
                                        <div class="flex-grow-1 text-truncate">
                                            <span class="small fw-semibold text-truncate d-block">
                                                {{ $page->title ?: ($typeLabel . ' #' . $page->seoable_id) }}
                                            </span>
                                        </div>
                                        <a href="{{ route('manager.seo.metas.edit', $page->id) }}"
                                           class="btn btn-sm btn-light flex-shrink-0">
                                            <i class="fas fa-pen fs-7"></i>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Acceso rápido ────────────────────────────────────────────────── --}}
        <div class="row g-3">

            <div class="col-12">
                <h6 class="fw-semibold text-muted text-uppercase mb-3 seo-section-label">
                    Acceso rápido
                </h6>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('manager.seo.metas.index') }}" class="text-decoration-none">
                    <div class="card w-100 h-100 border-0 shadow-sm seo-quick-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle flex-shrink-0">
                                <i class="fas fa-tags text-primary fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">Metas SEO</h6>
                                <p class="text-muted small mb-0">Gestionar títulos y descripciones</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('manager.seo.redirects.index') }}" class="text-decoration-none">
                    <div class="card w-100 h-100 border-0 shadow-sm seo-quick-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center bg-info-subtle flex-shrink-0">
                                <i class="fas fa-route text-info fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">Redirects</h6>
                                <p class="text-muted small mb-0">Gestionar redirecciones 301/302</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('manager.seo.logs.index') }}" class="text-decoration-none">
                    <div class="card w-100 h-100 border-0 shadow-sm seo-quick-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center brand-box-red flex-shrink-0">
                                <i class="fas fa-exclamation-triangle fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">Errores 404</h6>
                                <p class="text-muted small mb-0">Revisar URLs no encontradas</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-md-6">
                <a href="{{ route('manager.settings.seo.index') }}" class="text-decoration-none">
                    <div class="card w-100 h-100 border-0 shadow-sm seo-quick-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center brand-box-dark flex-shrink-0">
                                <i class="fas fa-sliders fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">Configuracion SEO</h6>
                                <p class="text-muted small mb-0">Robots, sitemap y ajustes generales</p>
                            </div>
                        </div>
                    </div>
                </a>
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

