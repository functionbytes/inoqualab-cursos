@extends('layouts.managers')

@section('title', 'Core Web Vitals')

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
            'LCP'  => ['fas fa-image',      'bg-primary-subtle',  'text-primary'],
            'INP'  => ['fas fa-hand-pointer','bg-info-subtle',     'text-info'],
            'CLS'  => ['fas fa-arrows-alt',  'bg-warning-subtle',  'text-warning'],
            'FCP'  => ['fas fa-paint-brush', 'bg-success-subtle',  'text-success'],
            'TTFB' => ['fas fa-server',      'bg-secondary-subtle','text-secondary'],
        ];
    @endphp

    <div class="widget-content">

        {{-- Periodo --}}
        <p class="text-muted mb-3">
            Datos del período: {{ $since->format('d/m/Y') }} — hoy
            &middot; <strong>{{ number_format($totalSamples) }}</strong> muestras totales
        </p>

        {{-- ── Tarjetas p75 globales ───────────────────────────────────────── --}}
        <div class="row g-3 mb-4">
            @foreach($metrics as $metric)
                @php
                    $data    = $globalP75[$metric] ?? null;
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

        <div class="row g-3">

            {{-- ── Páginas con peor rendimiento ───────────────────────────────── --}}
            <div class="col-12 col-lg-7">
                <div class="card h-100">
                    <div class="card-header p-4 border-bottom border-light">
                        <h5 class="mb-0 fw-bold">Páginas con peor rendimiento</h5>
                    </div>
                    <div class="card-body p-0">
                        @if($worstPages->isEmpty())
                            <div class="text-center py-5">
                                <i class="fas fa-chart-bar fa-2x text-muted opacity-50 mb-3"></i>
                                <p class="text-muted mb-0">No hay datos disponibles</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>URL</th>
                                            <th>Métrica</th>
                                            <th>Promedio</th>
                                            <th>Muestras</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($worstPages as $row)
                                            @php
                                                $isCls     = $row->metric === 'CLS';
                                                $avgFmt    = $isCls
                                                    ? number_format($row->avg_value, 3)
                                                    : number_format($row->avg_value) . ' ms';
                                                // route() rechaza un parámetro vacío: la home ("/") da
                                                // ltrim('/','/') === '' y revienta con UrlGenerationException.
                                                $pathParam = ltrim($row->url_path, '/');
                                                $detailUrl = route('manager.seo.web-vitals.show', [
                                                    'path' => $pathParam !== '' ? $pathParam : '_root',
                                                ]);
                                            @endphp
                                            <tr>
                                                <td class="text-truncate worst-page-url">
                                                    <a href="{{ $detailUrl }}" class="text-decoration-none small fw-semibold">
                                                        {{ $row->url_path }}
                                                    </a>
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

            {{-- ── Por dispositivo ─────────────────────────────────────────────── --}}
            <div class="col-12 col-lg-5">
                <div class="card h-100">
                    <div class="card-header p-4 border-bottom border-light">
                        <h5 class="mb-0 fw-bold">Por dispositivo</h5>
                    </div>
                    <div class="card-body p-0">
                        @if($byDevice->isEmpty())
                            <div class="text-center py-5">
                                <i class="fas fa-mobile-alt fa-2x text-muted opacity-50 mb-3"></i>
                                <p class="text-muted mb-0">No hay datos disponibles</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Dispositivo</th>
                                            <th>Métrica</th>
                                            <th>Promedio</th>
                                            <th>Muestras</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($byDevice as $row)
                                            @php
                                                $isCls  = $row->metric === 'CLS';
                                                $avgFmt = $isCls
                                                    ? number_format($row->avg_value, 3)
                                                    : number_format($row->avg_value) . ' ms';
                                                $deviceIcon = match($row->device) {
                                                    'mobile'  => 'fas fa-mobile-alt text-primary',
                                                    'tablet'  => 'fas fa-tablet-alt text-info',
                                                    default   => 'fas fa-desktop text-secondary',
                                                };
                                            @endphp
                                            <tr>
                                                <td>
                                                    <i class="{{ $deviceIcon }} me-1"></i>
                                                    <span class="small text-capitalize">{{ $row->device }}</span>
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

        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/web-vitals/shared.css') }}">
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/web-vitals/index.css') }}">
@endpush
