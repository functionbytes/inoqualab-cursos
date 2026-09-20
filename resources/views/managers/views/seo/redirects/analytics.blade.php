@extends('layouts.managers')

@section('title', 'Analytics — ' . $seoRedirect->source_path)

@section('content')


    <div class="row g-4">

        {{-- Info card --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header p-4 border-bottom border-light">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="mb-1 fw-bold">Estadisticas de redirect</h5>
                            <p class="mb-0 text-muted small">Actividad de hits para esta redirección</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="btn-test-redirect">
                                Probar redirect
                            </button>
                            <a href="{{ route('manager.seo.redirects.index') }}" class="btn btn-secondary">
                                Volver
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">URL de origen</p>
                            <code class="fs-6 text-break">{{ $seoRedirect->source_path }}</code>
                        </div>
                        <div class="col-12 col-md-6">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">URL de destino</p>
                            <code class="fs-6 text-break">{{ $seoRedirect->target_path }}</code>
                        </div>
                        <div class="col-6 col-md-3">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">Codigo</p>
                            <span class="badge fs-6 {{ $seoRedirect->status_code === 301 ? 'bg-primary' : 'bg-info' }}">
                                {{ $seoRedirect->status_code }}
                            </span>
                        </div>
                        <div class="col-6 col-md-3">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">Total hits</p>
                            <span class="fs-5 fw-bold">{{ number_format($seoRedirect->hits_count ?? 0) }}</span>
                        </div>
                        <div class="col-12 col-md-6 js-hidden" id="test-result-container">
                            <p class="text-muted small mb-1 fw-semibold text-uppercase">Resultado del test</p>
                            <div id="test-result"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart card --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header p-4 border-bottom border-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Hits en los ultimos 30 dias</h5>
                        <p class="mb-0 text-muted small" id="chart-subtitle">Cargando datos...</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-reload-chart">
                        Actualizar
                    </button>
                </div>
                <div class="card-body">
                    <div id="chart-container" class="redirect-chart-container"
                         data-analytics-url="{{ route('manager.seo.redirects.analytics', $seoRedirect) }}"
                         data-test-url="{{ route('manager.seo.redirects.test', $seoRedirect) }}">
                        <div class="d-flex align-items-center justify-content-center h-100 py-5" id="chart-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                        <canvas id="hits-chart" class="js-hidden"></canvas>
                        <div id="chart-table-fallback" class="js-hidden">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th class="text-end">Hits</th>
                                        </tr>
                                    </thead>
                                    <tbody id="chart-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/redirects/analytics.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/redirects/analytics.js') }}"></script>
@endpush

