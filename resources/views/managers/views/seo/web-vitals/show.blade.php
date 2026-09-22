@extends('layouts.managers')

@section('title', 'Core Web Vitals — Detalle')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Tendencia histórica (28 días)',
    ])
@endsection

@section('content')


    @php
        $metrics = ['LCP', 'INP', 'CLS', 'FCP', 'TTFB'];

        $ratingClasses = [
            'good'              => 'bg-success',
            'needs-improvement' => 'bg-warning text-dark',
            'poor'              => 'bg-danger',
            'unknown'           => 'bg-secondary',
        ];

        $ratingLabels = [
            'good'              => 'Bueno',
            'needs-improvement' => 'Mejorar',
            'poor'              => 'Malo',
            'unknown'           => 'Sin datos',
        ];

        $metricIcons = [
            'LCP'  => ['fas fa-image',       'bg-primary-subtle',   'text-primary'],
            'INP'  => ['fas fa-hand-pointer', 'bg-info-subtle',      'text-info'],
            'CLS'  => ['fas fa-arrows-alt',   'bg-warning-subtle',   'text-warning'],
            'FCP'  => ['fas fa-paint-brush',  'bg-success-subtle',   'text-success'],
            'TTFB' => ['fas fa-server',       'bg-secondary-subtle', 'text-secondary'],
        ];
    @endphp

    <div class="widget-content">

        {{-- Encabezado de URL + volver --}}
        <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
            <a href="{{ route('manager.seo.web-vitals.index') }}" class="btn btn-light btn-sm">
                Volver
            </a>
            <div>
                <span class="text-muted small">URL analizada:</span>
                <strong class="ms-1">{{ $normalizedPath }}</strong>
            </div>
            <span class="text-muted small ms-auto">
                Desde {{ $since->format('d/m/Y') }} hasta hoy
            </span>
        </div>

        {{-- ── Tarjetas p75 para esta URL ──────────────────────────────────── --}}
        <div class="row g-3 mb-4">
            @foreach($metrics as $metric)
                @php
                    $data    = $p75[$metric] ?? null;
                    $rating  = $data['rating'] ?? 'unknown';
                    $value   = $data['value']  ?? null;
                    $samples = $data['samples'] ?? 0;
                    $isCls   = $metric === 'CLS';

                    if ($value !== null) {
                        $formatted = $isCls
                            ? number_format($value, 3)
                            : number_format($value) . ' ms';
                    } else {
                        $formatted = '—';
                    }

                    [$icon, $bgClass, $textClass] = $metricIcons[$metric];
                    $badgeClass = $ratingClasses[$rating] ?? 'bg-secondary';
                    $badgeLabel = $ratingLabels[$rating]  ?? 'Sin datos';
                @endphp
                <div class="col-6 col-md-4 col-xl">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="cwv-icon-box rounded-2 d-flex align-items-center justify-content-center {{ $bgClass }} flex-shrink-0">
                                    <i class="{{ $icon }} {{ $textClass }}"></i>
                                </div>
                                <span class="fw-semibold">{{ $metric }}</span>
                            </div>
                            <div class="h4 fw-bold mb-1">{{ $formatted }}</div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                <p class="text-muted">{{ number_format($samples) }} muestras</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── Tendencia histórica ──────────────────────────────────────────── --}}
        <div class="card">
            
            <div class="card-body p-0">
                @if($trend->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line fa-2x text-muted opacity-50 mb-3"></i>
                        <p class="text-muted mb-0">No hay datos de tendencia para esta URL</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Métrica</th>
                                    <th>Promedio</th>
                                    <th>Muestras</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trend as $row)
                                    @php
                                        $isCls  = $row->metric === 'CLS';
                                        $avgFmt = $isCls
                                            ? number_format($row->avg_value, 3)
                                            : number_format($row->avg_value) . ' ms';
                                        $date   = \Carbon\Carbon::parse($row->date)->format('d/m/Y');
                                    @endphp
                                    <tr>
                                        <td>
                                            <p class="text-muted">{{ $date }}</p>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $row->metric }}</span>
                                        </td>
                                        <td class="fw-semibold">{{ $avgFmt }}</td>
                                        <td>
                                            <p class="text-muted">{{ number_format($row->samples) }}</p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/web-vitals/shared.css') }}">
@endpush
