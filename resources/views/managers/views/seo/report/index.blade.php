@extends('layouts.managers')

@section('title', 'Reporte SEO')

@section('content')


    @php
        $gradeConfig = [
            'A' => ['label' => 'A (90–100)', 'class' => 'bg-success'],
            'B' => ['label' => 'B (75–89)',  'class' => 'bg-info'],
            'C' => ['label' => 'C (60–74)',  'class' => 'bg-warning text-dark'],
            'D' => ['label' => 'D (40–59)',  'class' => 'bg-orange'],
            'F' => ['label' => 'F (<40)',    'class' => 'bg-danger'],
        ];

        $gradeDistribution = $stats['grade_distribution'] ?? [];
        $gradeTotal = array_sum($gradeDistribution);
    @endphp

    <div class="widget-content">

        {{-- ── Tarjetas de resumen ──────────────────────────────────────────── --}}
        <div class="row g-3 mb-4">

            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle flex-shrink-0">
                                <i class="fas fa-tags text-primary"></i>
                            </div>
                            <span class="text-muted small">Total metas</span>
                        </div>
                        <div class="h4 fw-bold mb-0">{{ number_format($stats['total_metas'] ?? 0) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center bg-success-subtle flex-shrink-0">
                                <i class="fas fa-star text-success"></i>
                            </div>
                            <span class="text-muted small">Score promedio</span>
                        </div>
                        <div class="h4 fw-bold mb-0">
                            {{ $stats['avg_score'] > 0 ? $stats['avg_score'] : '—' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center bg-info-subtle flex-shrink-0">
                                <i class="fas fa-route text-info"></i>
                            </div>
                            <span class="text-muted small">Total redirects</span>
                        </div>
                        <div class="h4 fw-bold mb-0">{{ number_format($stats['total_redirects'] ?? 0) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="seo-icon-box rounded-2 d-flex align-items-center justify-content-center bg-warning-subtle flex-shrink-0">
                                <i class="fas fa-chart-pie text-warning"></i>
                            </div>
                            <span class="text-muted small">Distribución grades</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($gradeConfig as $grade => $cfg)
                                @php $count = $gradeDistribution[$grade] ?? 0; @endphp
                                @if($count > 0)
                                    <span class="badge {{ $cfg['class'] }}">
                                        {{ $grade }}: {{ number_format($count) }}
                                    </span>
                                @endif
                            @endforeach
                            @if($gradeTotal === 0)
                                <span class="text-muted small">Sin datos</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-3">

            {{-- ── Exportar datos ───────────────────────────────────────────────── --}}
            <div class="col-12 col-lg-4">
                <div class="card h-100">
                    <div class="card-header p-4 border-bottom border-light">
                        <h5 class="mb-0 fw-bold">Exportar datos</h5>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">

                        <div class="border rounded p-3">
                            <h6 class="fw-semibold mb-1">Metas SEO</h6>
                            <p class="text-muted small mb-3">
                                Exporta todos los metadatos SEO (título, descripción, robots, score).
                            </p>
                            <a href="{{ route('manager.seo.report.export') }}" class="btn btn-primary w-100">
                                Exportar CSV (metas)
                            </a>
                        </div>

                        <div class="border rounded p-3">
                            <h6 class="fw-semibold mb-1">Reporte completo</h6>
                            <p class="text-muted small mb-3">
                                Incluye metas, redirects y estadísticas combinadas.
                            </p>
                            <a href="{{ route('manager.seo.report.export', ['format' => 'full']) }}" class="btn btn-outline-primary w-100">
                                Exportar CSV completo
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Distribución de grades ───────────────────────────────────────── --}}
            <div class="col-12 col-lg-8">
                <div class="card h-100">
                    <div class="card-header p-4 border-bottom border-light">
                        <h5 class="mb-0 fw-bold">Distribución de grades</h5>
                    </div>
                    <div class="card-body">
                        @if($gradeTotal === 0)
                            <div class="text-center py-5">
                                <i class="fas fa-chart-bar fa-2x text-muted opacity-50 mb-3"></i>
                                <p class="text-muted mb-0">No hay datos de score disponibles</p>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach($gradeConfig as $grade => $cfg)
                                    @php
                                        $count   = $gradeDistribution[$grade] ?? 0;
                                        $percent = $gradeTotal > 0 ? round(($count / $gradeTotal) * 100) : 0;
                                    @endphp
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge {{ $cfg['class'] }}" style="min-width:24px;">{{ $grade }}</span>
                                                <span class="small text-muted">{{ $cfg['label'] }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-semibold small">{{ number_format($count) }}</span>
                                                <span class="text-muted small">({{ $percent }}%)</span>
                                            </div>
                                        </div>
                                        <div class="progress" style="height:8px;">
                                            <div class="progress-bar {{ $cfg['class'] }}"
                                                 role="progressbar"
                                                 style="width: {{ $percent }}%"
                                                 aria-valuenow="{{ $percent }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Total páginas auditadas</span>
                                <span class="fw-bold">{{ number_format($gradeTotal) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('css')
<style>
    .seo-icon-box { width: 36px; height: 36px; }
    .bg-orange { background-color: #fd7e14; color: #fff; }
</style>
@endpush
