@extends('layouts.managers')

@section('title', 'Auditoria SEO')

@section('content')


    <div class="widget-content"
         id="audit-config"
         data-audit-url="{{ route('manager.seo.audit.url') }}"
         data-core-web-vitals-url="{{ route('manager.seo.audit.core-web-vitals') }}"
         data-bulk-start-url="{{ route('manager.seo.audit.bulk-start') }}"
         data-bulk-progress-url="{{ route('manager.seo.audit.bulk-progress') }}"
         data-bulk-all-url="{{ route('manager.seo.audit.all') }}"
         data-meta-edit-url-template="{{ route('manager.seo.metas.edit', ':id') }}"
         data-check-canonicals-url="{{ route('manager.seo.audit.check-canonicals') }}"
         data-broken-links-start-url="{{ route('manager.seo.audit.broken-links-start') }}"
         data-broken-links-progress-url="{{ route('manager.seo.audit.broken-links-progress') }}"
         data-internal-links-url="{{ route('manager.seo.audit.internal-links') }}">

        {{-- ── Auditoría por URL ────────────────────────────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header p-4 border-bottom border-light">
                <h5 class="mb-1 fw-bold">Auditoría por URL</h5>
                <p class="mb-0 text-muted">Analiza una URL específica y obtén el score SEO con detalles</p>
            </div>
            <div class="card-body">
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-md">
                        <label class="form-label fw-semibold">URL a auditar</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-link text-muted"></i>
                            </span>
                            <input type="url" id="audit-url-input" class="form-control border-start-0 ps-0"
                                   placeholder="https://ejemplo.com/mi-pagina">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button type="button" class="btn btn-primary w-100" id="btn-audit-url">
                            Auditar URL
                        </button>
                    </div>
                </div>

                {{-- Resultado de auditoría por URL --}}
                <div id="url-audit-result" class="d-none">
                    <hr class="mb-3">
                    <div class="row g-3 align-items-start">

                        {{-- Score circular --}}
                        <div class="col-12 col-md-3 text-center">
                            <div class="seo-score-circle mx-auto mb-2" id="url-score-circle">
                                <span id="url-score-value" class="seo-score-number">—</span>
                            </div>
                            <span id="url-grade-badge" class="badge fs-6 px-3 py-2">—</span>
                            <p class="text-muted small mt-2 mb-0" id="url-score-label">Score SEO</p>
                        </div>

                        {{-- Issues y passed --}}
                        <div class="col-12 col-md-9">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <h6 class="fw-semibold mb-2 text-danger">
                                        <i class="fas fa-circle-xmark me-1"></i>
                                        Problemas detectados
                                        <span id="url-issues-count" class="badge bg-danger ms-1">0</span>
                                    </h6>
                                    <ul class="list-group list-group-flush" id="url-issues-list">
                                        <li class="list-group-item text-muted small px-0">Sin resultados</li>
                                    </ul>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <h6 class="fw-semibold mb-2 text-success">
                                        <i class="fas fa-circle-check me-1"></i>
                                        Verificaciones correctas
                                        <span id="url-passed-count" class="badge bg-success ms-1">0</span>
                                    </h6>
                                    <ul class="list-group list-group-flush" id="url-passed-list">
                                        <li class="list-group-item text-muted small px-0">Sin resultados</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ── PageSpeed Insights ───────────────────────────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header p-4 border-bottom border-light">
                <h5 class="mb-1 fw-bold">PageSpeed Insights</h5>
                <p class="mb-0 text-muted">Scores de Lighthouse y Core Web Vitals de laboratorio para una URL puntual</p>
            </div>
            <div class="card-body">
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-md">
                        <label class="form-label fw-semibold">URL</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-link text-muted"></i>
                            </span>
                            <input type="url" id="pagespeed-url-input" class="form-control border-start-0 ps-0"
                                   placeholder="https://ejemplo.com/mi-pagina">
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <label class="form-label fw-semibold">Dispositivo</label>
                        <select id="pagespeed-strategy" class="form-select">
                            <option value="mobile">Móvil</option>
                            <option value="desktop">Escritorio</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button type="button" class="btn btn-primary w-100" id="btn-pagespeed">
                            Analizar
                        </button>
                    </div>
                </div>

                <div id="pagespeed-result" class="d-none">
                    <hr class="mb-3">
                    <div class="row g-3 text-center mb-3">
                        <div class="col-6 col-md-3">
                            <h4 class="mb-0 fw-bold" id="ps-performance">—</h4>
                            <span class="text-muted small">Performance</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <h4 class="mb-0 fw-bold" id="ps-seo">—</h4>
                            <span class="text-muted small">SEO</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <h4 class="mb-0 fw-bold" id="ps-accessibility">—</h4>
                            <span class="text-muted small">Accesibilidad</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <h4 class="mb-0 fw-bold" id="ps-best-practices">—</h4>
                            <span class="text-muted small">Buenas prácticas</span>
                        </div>
                    </div>
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-2"><span class="text-muted small d-block">LCP</span><strong id="ps-lcp">—</strong></div>
                        <div class="col-6 col-md-2"><span class="text-muted small d-block">CLS</span><strong id="ps-cls">—</strong></div>
                        <div class="col-6 col-md-2"><span class="text-muted small d-block">FCP</span><strong id="ps-fcp">—</strong></div>
                        <div class="col-6 col-md-2"><span class="text-muted small d-block">TTFB</span><strong id="ps-ttfb">—</strong></div>
                        <div class="col-6 col-md-2"><span class="text-muted small d-block">TBT</span><strong id="ps-fid">—</strong></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Auditoría masiva de todas las metas ──────────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1 fw-bold">Auditoría masiva de metas</h5>
                        <p class="mb-0 text-muted">Audita todas las metas SEO del sistema y genera scores</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" id="btn-load-all-audits">
                            Ver últimos resultados
                        </button>
                        <button type="button" class="btn btn-primary" id="btn-bulk-start">
                            Auditar todas las metas
                        </button>
                    </div>
                </div>
            </div>

            {{-- Progreso --}}
            <div class="card-body border-bottom d-none" id="bulk-progress-section">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="fw-semibold">Procesando auditoría masiva...</span>
                    <span id="bulk-progress-text" class="text-muted small ms-auto">0 / 0</span>
                </div>
                <div class="progress progress-thin">
                    <div id="bulk-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                         role="progressbar"></div>
                </div>
            </div>

            {{-- Resumen --}}
            <div id="bulk-summary-section" class="d-none">
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body text-center py-3">
                                    <h6 class="card-title mb-1 text-muted small">Total</h6>
                                    <h4 class="mb-0 fw-bold" id="bulk-stat-total">—</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body text-center py-3">
                                    <h6 class="card-title mb-1 text-muted small">Score promedio</h6>
                                    <h4 class="mb-0 fw-bold" id="bulk-stat-avg">—</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body text-center py-3">
                                    <h6 class="card-title mb-1 text-muted small">Con issues</h6>
                                    <h4 class="mb-0 fw-bold text-warning" id="bulk-stat-issues">—</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body text-center py-3">
                                    <h6 class="card-title mb-1 text-muted small">Grade A</h6>
                                    <h4 class="mb-0 fw-bold text-success" id="bulk-stat-a">—</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla de resultados --}}
            <div class="card-body p-0 js-hidden" id="bulk-results-section">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Meta / URL</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Grade</th>
                                <th class="text-center">Issues</th>
                                <th class="text-center">Passed</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="bulk-results-tbody">
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="bulk-empty" class="card-body text-center py-5 d-none">
                <i class="fas fa-magnifying-glass fa-3x mb-3 text-muted opacity-50"></i>
                <h5 class="fw-bold mb-2">Sin resultados de auditoría</h5>
                <p class="text-muted mb-0">Ejecuta la auditoría masiva para ver los resultados</p>
            </div>
        </div>

        {{-- ── Verificar canonicals ─────────────────────────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1 fw-bold">Verificar canonicals</h5>
                        <p class="mb-0 text-muted">Comprueba el estado HTTP de cada canonical configurado</p>
                    </div>
                    <button type="button" class="btn btn-primary" id="btn-check-canonicals">
                        Verificar canonicals
                    </button>
                </div>
            </div>

            {{-- Progreso canonicals --}}
            <div class="card-body border-bottom d-none" id="canonical-progress-section">
                <div class="d-flex align-items-center gap-2">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="fw-semibold">Verificando canonicals...</span>
                </div>
            </div>

            {{-- Resumen canonicals --}}
            <div id="canonical-summary" class="d-none">
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body text-center py-3">
                                    <h6 class="text-muted small mb-1">Total</h6>
                                    <h4 class="fw-bold mb-0" id="canonical-stat-total">—</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body text-center py-3">
                                    <h6 class="text-muted small mb-1">Correctos</h6>
                                    <h4 class="fw-bold mb-0 text-success" id="canonical-stat-ok">—</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body text-center py-3">
                                    <h6 class="text-muted small mb-1">Rotos</h6>
                                    <h4 class="fw-bold mb-0 text-danger" id="canonical-stat-broken">—</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla canonicals --}}
            <div class="card-body p-0 js-hidden" id="canonical-results-section">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Meta</th>
                                <th>URL canonical</th>
                                <th class="text-center">Estado HTTP</th>
                            </tr>
                        </thead>
                        <tbody id="canonical-results-tbody">
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="canonical-empty" class="card-body text-center py-5 d-none">
                <i class="fas fa-link fa-3x mb-3 text-muted opacity-50"></i>
                <h5 class="fw-bold mb-2">Sin datos de canonicals</h5>
                <p class="text-muted mb-0">Ejecuta la verificación para ver el estado</p>
            </div>
        </div>

        {{-- ── Links rotos ──────────────────────────────────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1 fw-bold">Links rotos</h5>
                        <p class="mb-0 text-muted">Detecta enlaces internos y externos que devuelven error</p>
                    </div>
                    <button type="button" class="btn btn-primary" id="btn-broken-links-start">
                        Verificar links rotos
                    </button>
                </div>
            </div>

            {{-- Progreso links rotos --}}
            <div class="card-body border-bottom d-none" id="broken-progress-section">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="fw-semibold">Verificando links...</span>
                    <span id="broken-progress-text" class="text-muted small ms-auto">0 / 0</span>
                </div>
                <div class="progress progress-thin">
                    <div id="broken-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                         role="progressbar"></div>
                </div>
            </div>

            {{-- Lista links rotos --}}
            <div class="card-body p-0 js-hidden" id="broken-results-section">
                <ul class="list-group list-group-flush" id="broken-links-list">
                </ul>
            </div>

            <div id="broken-empty" class="card-body text-center py-5 d-none">
                <i class="fas fa-circle-check fa-3x mb-3 text-success opacity-75"></i>
                <h5 class="fw-bold mb-2">No se encontraron links rotos</h5>
                <p class="text-muted mb-0">Todos los enlaces verificados responden correctamente</p>
            </div>
        </div>

        {{-- ── Links internos ───────────────────────────────────────────────── --}}
        <div class="card mb-3">
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1 fw-bold">Links internos</h5>
                        <p class="mb-0 text-muted">Detecta páginas huérfanas y analiza los enlaces de entrada</p>
                    </div>
                    <button type="button" class="btn btn-primary" id="btn-internal-links">
                        Analizar links internos
                    </button>
                </div>
            </div>

            {{-- Progreso internal links --}}
            <div class="card-body border-bottom d-none" id="internal-progress-section">
                <div class="d-flex align-items-center gap-2">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="fw-semibold">Analizando estructura de links internos...</span>
                </div>
            </div>

            {{-- Resultados links internos --}}
            <div id="internal-results-section" class="js-hidden">
                <div class="card-body border-bottom">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body text-center py-3">
                                    <h6 class="text-muted small mb-1">URLs escaneadas</h6>
                                    <h4 class="fw-bold mb-0" id="internal-stat-scanned">—</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body text-center py-3">
                                    <h6 class="text-muted small mb-1">Total URLs</h6>
                                    <h4 class="fw-bold mb-0" id="internal-stat-total">—</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body text-center py-3">
                                    <h6 class="text-muted small mb-1">Huérfanas</h6>
                                    <h4 class="fw-bold mb-0 text-warning" id="internal-stat-orphans">—</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        {{-- Páginas huérfanas --}}
                        <div class="col-12 col-lg-6">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-unlink text-warning me-1"></i>
                                Páginas huérfanas
                            </h6>
                            <ul class="list-group list-group-flush" id="orphans-list">
                                <li class="list-group-item text-muted small px-0">Sin datos</li>
                            </ul>
                        </div>
                        {{-- Top inbound --}}
                        <div class="col-12 col-lg-6">
                            <h6 class="fw-semibold mb-3">
                                <i class="fas fa-arrow-down text-primary me-1"></i>
                                Top enlaces entrantes
                            </h6>
                            <ul class="list-group list-group-flush" id="inbound-list">
                                <li class="list-group-item text-muted small px-0">Sin datos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div id="internal-empty" class="card-body text-center py-5 d-none">
                <i class="fas fa-sitemap fa-3x mb-3 text-muted opacity-50"></i>
                <h5 class="fw-bold mb-2">Sin análisis de links internos</h5>
                <p class="text-muted mb-0">Ejecuta el análisis para ver la estructura de enlaces</p>
            </div>
        </div>

        {{-- Acceso rápido --}}
        <div class="d-flex justify-content-end">
            <a href="{{ route('manager.seo.audit.history') }}" class="btn btn-outline-secondary">
                Ver historial de auditorías
            </a>
        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/audit/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/seo/audit/index.js') }}"></script>
@endpush

