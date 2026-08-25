@extends('layouts.managers')

@section('title', 'Auditoria SEO')

@section('content')


    <div class="widget-content">

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
                <div class="progress" style="height:8px;">
                    <div id="bulk-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                         role="progressbar" style="width:0%"></div>
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
            <div class="card-body p-0" id="bulk-results-section" style="display:none;">
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
            <div class="card-body p-0" id="canonical-results-section" style="display:none;">
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
                <div class="progress" style="height:8px;">
                    <div id="broken-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                         role="progressbar" style="width:0%"></div>
                </div>
            </div>

            {{-- Lista links rotos --}}
            <div class="card-body p-0" id="broken-results-section" style="display:none;">
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
            <div id="internal-results-section" style="display:none;">
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
                <i class="fas fa-history me-1"></i>
                Ver historial de auditorías
            </a>
        </div>

    </div>

@endsection

@push('css')
<style>
    .seo-score-circle {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: #f5f6f8;
        border: 4px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color 0.3s ease;
    }
    .seo-score-circle.grade-a { border-color: #198754; background: #d1e7dd; }
    .seo-score-circle.grade-b { border-color: #0d6efd; background: #cfe2ff; }
    .seo-score-circle.grade-c { border-color: #ffc107; background: #fff3cd; }
    .seo-score-circle.grade-d,
    .seo-score-circle.grade-f { border-color: #dc3545; background: #f8d7da; }
    .seo-score-number {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
    }
</style>
@endpush

@push('scripts')
<script>
$(function () {

    var csrfToken  = $('meta[name="csrf-token"]').attr('content');
    var gradeMap   = { A: 'success', B: 'primary', C: 'warning', D: 'danger', F: 'danger' };
    var bulkTimer  = null;
    var brokenTimer = null;

    // ── helpers ───────────────────────────────────────────────────────────────
    function gradeClass(grade) {
        return gradeMap[grade] ?? 'secondary';
    }

    function scoreToGrade(score) {
        if (score >= 90) return 'A';
        if (score >= 75) return 'B';
        if (score >= 60) return 'C';
        if (score >= 40) return 'D';
        return 'F';
    }

    function renderGradeBadge(grade) {
        var cls = gradeClass(grade);
        return '<span class="badge bg-' + cls + ' px-2">' + grade + '</span>';
    }

    // ── Auditoría por URL ─────────────────────────────────────────────────────
    $('#btn-audit-url').on('click', function () {
        var url    = $('#audit-url-input').val().trim();
        var $btn   = $(this);
        var $input = $('#audit-url-input');

        $input.removeClass('is-invalid');

        if (!url) {
            $input.addClass('is-invalid');
            $input.next('.invalid-feedback').text('Ingresa una URL válida.');
            return;
        }

        $btn.prop('disabled', true).text('Auditando...');
        $('#url-audit-result').addClass('d-none');

        $.ajax({
            url: '{{ route("manager.seo.audit.url") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { url: url },
            success: function (res) {
                renderUrlResult(res.data);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $input.addClass('is-invalid')
                          .next('.invalid-feedback').text(xhr.responseJSON.errors?.url?.[0] ?? 'URL inválida.');
                } else {
                    toastr.error(xhr.responseJSON?.message ?? 'Error al auditar la URL.');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('Auditar URL');
            }
        });
    });

    function renderUrlResult(data) {
        var grade      = scoreToGrade(data.score);
        var cls        = gradeClass(grade);
        var $circle    = $('#url-score-circle');

        $circle.removeClass('grade-a grade-b grade-c grade-d grade-f')
               .addClass('grade-' + grade.toLowerCase());
        $('#url-score-value').text(data.score);
        $('#url-grade-badge').removeClass().addClass('badge fs-6 px-3 py-2 bg-' + cls)
                             .text('Grade ' + grade);

        renderCheckList('#url-issues-list', data.issues, 'danger');
        renderCheckList('#url-passed-list', data.passed, 'success');

        $('#url-issues-count').text(data.issues.length);
        $('#url-passed-count').text(data.passed.length);
        $('#url-audit-result').removeClass('d-none');
    }

    // ── PageSpeed Insights ────────────────────────────────────────────────────
    $('#btn-pagespeed').on('click', function () {
        var url = $('#pagespeed-url-input').val().trim();
        var strategy = $('#pagespeed-strategy').val();
        var $btn = $(this);

        if (!url) {
            toastr.error('Ingresa una URL válida.');
            return;
        }

        $btn.prop('disabled', true).text('Analizando...');
        $('#pagespeed-result').addClass('d-none');

        $.ajax({
            url: '{{ route("manager.seo.audit.core-web-vitals") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { url: url, strategy: strategy },
            success: function (res) {
                $('#ps-performance').text(res.performance_score);
                $('#ps-seo').text(res.seo_score);
                $('#ps-accessibility').text(res.accessibility_score);
                $('#ps-best-practices').text(res.best_practices_score);
                $('#ps-lcp').text(res.lcp);
                $('#ps-cls').text(res.cls);
                $('#ps-fcp').text(res.fcp);
                $('#ps-ttfb').text(res.ttfb);
                $('#ps-fid').text(res.fid);
                $('#pagespeed-result').removeClass('d-none');
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.error ?? xhr.responseJSON?.message ?? 'Error al analizar la URL.');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Analizar');
            }
        });
    });

    function renderCheckList(selector, items, type) {
        var $list = $(selector).empty();
        if (!items || !items.length) {
            $list.append('<li class="list-group-item text-muted small px-0">Ninguno</li>');
            return;
        }
        items.forEach(function (item) {
            $list.append(
                '<li class="list-group-item px-0 py-1 border-0 border-bottom">' +
                '<i class="fas fa-circle-' + (type === 'danger' ? 'xmark text-danger' : 'check text-success') + ' me-2 small"></i>' +
                '<span class="small">' + $('<div>').text(item).html() + '</span>' +
                '</li>'
            );
        });
    }

    // ── Auditoría masiva — iniciar job ────────────────────────────────────────
    $('#btn-bulk-start').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Iniciando...');

        $.ajax({
            url: '{{ route("manager.seo.audit.bulk-start") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () {
                toastr.info('Auditoría masiva iniciada.');
                $('#bulk-progress-section').removeClass('d-none');
                $('#bulk-summary-section, #bulk-empty').addClass('d-none');
                $('#bulk-results-section').hide();
                pollBulkProgress();
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al iniciar la auditoría.');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Auditar todas las metas');
            }
        });
    });

    function pollBulkProgress() {
        clearInterval(bulkTimer);
        bulkTimer = setInterval(function () {
            $.getJSON('{{ route("manager.seo.audit.bulk-progress") }}', function (res) {
                var pct = res.total > 0 ? Math.round((res.processed / res.total) * 100) : 0;
                $('#bulk-progress-bar').css('width', pct + '%');
                $('#bulk-progress-text').text(res.processed + ' / ' + res.total);

                if (res.status === 'done' || res.processed >= res.total) {
                    clearInterval(bulkTimer);
                    $('#bulk-progress-section').addClass('d-none');
                    toastr.success('Auditoría masiva completada.');
                    loadAllAuditResults();
                }
            });
        }, 2000);
    }

    // ── Ver últimos resultados de auditoría masiva ────────────────────────────
    $('#btn-load-all-audits').on('click', function () {
        loadAllAuditResults();
    });

    function loadAllAuditResults() {
        $.getJSON('{{ route("manager.seo.audit.all") }}', function (res) {
            if (!res.data || !res.data.length) {
                $('#bulk-empty').removeClass('d-none');
                $('#bulk-results-section').hide();
                $('#bulk-summary-section').addClass('d-none');
                return;
            }

            var s = res.summary;
            $('#bulk-stat-total').text(s.total ?? 0);
            $('#bulk-stat-avg').text(s.avg_score ?? '—');
            $('#bulk-stat-issues').text(s.with_issues ?? 0);
            $('#bulk-stat-a').text(s.score_a ?? 0);
            $('#bulk-summary-section').removeClass('d-none');

            var $tbody = $('#bulk-results-tbody').empty();
            res.data.forEach(function (row) {
                var grade = scoreToGrade(row.score);
                var cls   = gradeClass(grade);
                $tbody.append(
                    '<tr>' +
                    '<td><code class="small">' + $('<div>').text(row.url ?? row.title ?? '—').html() + '</code></td>' +
                    '<td class="text-center"><strong>' + row.score + '</strong></td>' +
                    '<td class="text-center">' + renderGradeBadge(grade) + '</td>' +
                    '<td class="text-center"><span class="badge bg-' + (row.issues_count > 0 ? 'danger' : 'light text-dark border') + '">' + (row.issues_count ?? 0) + '</span></td>' +
                    '<td class="text-center"><span class="badge bg-success">' + (row.passed_count ?? 0) + '</span></td>' +
                    '<td class="text-center">' +
                    (row.meta_id ? '<a href="' + '{{ route('manager.seo.metas.edit', ':id') }}'.replace(':id', row.meta_id) + '" class="btn btn-sm btn-light">Ver meta</a>' : '—') +
                    '</td>' +
                    '</tr>'
                );
            });

            $('#bulk-results-section').show();
            $('#bulk-empty').addClass('d-none');
        }).fail(function () {
            toastr.error('Error al cargar los resultados.');
        });
    }

    // ── Verificar canonicals ──────────────────────────────────────────────────
    $('#btn-check-canonicals').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Verificando...');

        $('#canonical-progress-section').removeClass('d-none');
        $('#canonical-summary, #canonical-empty').addClass('d-none');
        $('#canonical-results-section').hide();

        $.getJSON('{{ route("manager.seo.audit.check-canonicals") }}', function (res) {
            $('#canonical-progress-section').addClass('d-none');

            var s = res.summary;
            $('#canonical-stat-total').text(s.total ?? 0);
            $('#canonical-stat-ok').text(s.ok ?? 0);
            $('#canonical-stat-broken').text(s.broken ?? 0);
            $('#canonical-summary').removeClass('d-none');

            if (!res.results || !res.results.length) {
                $('#canonical-empty').removeClass('d-none');
            } else {
                var $tbody = $('#canonical-results-tbody').empty();
                res.results.forEach(function (row) {
                    var isOk  = row.status_code >= 200 && row.status_code < 400;
                    var badge = isOk
                        ? '<span class="badge bg-success">' + row.status_code + '</span>'
                        : '<span class="badge bg-danger">' + (row.status_code ?? 'Error') + '</span>';
                    $tbody.append(
                        '<tr>' +
                        '<td><small class="fw-semibold">' + $('<div>').text(row.title ?? '—').html() + '</small></td>' +
                        '<td><code class="small">' + $('<div>').text(row.canonical ?? '—').html() + '</code></td>' +
                        '<td class="text-center">' + badge + '</td>' +
                        '</tr>'
                    );
                });
                $('#canonical-results-section').show();
            }
        }).fail(function () {
            toastr.error('Error al verificar los canonicals.');
            $('#canonical-progress-section').addClass('d-none');
        }).always(function () {
            $btn.prop('disabled', false).text('Verificar canonicals');
        });
    });

    // ── Links rotos ───────────────────────────────────────────────────────────
    $('#btn-broken-links-start').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Iniciando...');

        $.ajax({
            url: '{{ route("manager.seo.audit.broken-links-start") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function () {
                toastr.info('Verificación de links iniciada.');
                $('#broken-progress-section').removeClass('d-none');
                $('#broken-empty').addClass('d-none');
                $('#broken-results-section').hide();
                pollBrokenProgress();
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al iniciar la verificación.');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Verificar links rotos');
            }
        });
    });

    function pollBrokenProgress() {
        clearInterval(brokenTimer);
        brokenTimer = setInterval(function () {
            $.getJSON('{{ route("manager.seo.audit.broken-links-progress") }}', function (res) {
                var pct = res.total > 0 ? Math.round((res.checked / res.total) * 100) : 0;
                $('#broken-progress-bar').css('width', pct + '%');
                $('#broken-progress-text').text(res.checked + ' / ' + res.total);

                if (res.status === 'done' || res.checked >= res.total) {
                    clearInterval(brokenTimer);
                    $('#broken-progress-section').addClass('d-none');

                    if (!res.broken || !res.broken.length) {
                        $('#broken-empty').removeClass('d-none');
                        toastr.success('No se encontraron links rotos.');
                    } else {
                        var $list = $('#broken-links-list').empty();
                        res.broken.forEach(function (link) {
                            $list.append(
                                '<li class="list-group-item d-flex align-items-center gap-3">' +
                                '<span class="badge bg-danger flex-shrink-0">' + (link.status_code ?? 'Error') + '</span>' +
                                '<div class="flex-grow-1 min-w-0">' +
                                '<code class="small text-break">' + $('<div>').text(link.url ?? '').html() + '</code>' +
                                (link.source ? '<div class="text-muted" style="font-size:0.75rem;">Desde: ' + $('<div>').text(link.source).html() + '</div>' : '') +
                                '</div>' +
                                '</li>'
                            );
                        });
                        $('#broken-results-section').show();
                        toastr.warning(res.broken.length + ' link(s) roto(s) encontrado(s).');
                    }
                }
            });
        }, 2000);
    }

    // ── Links internos ────────────────────────────────────────────────────────
    $('#btn-internal-links').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Analizando...');

        $('#internal-progress-section').removeClass('d-none');
        $('#internal-empty').addClass('d-none');
        $('#internal-results-section').hide();

        $.getJSON('{{ route("manager.seo.audit.internal-links") }}', function (res) {
            $('#internal-progress-section').addClass('d-none');

            $('#internal-stat-scanned').text(res.scanned ?? 0);
            $('#internal-stat-total').text(res.total_urls ?? 0);
            $('#internal-stat-orphans').text(res.orphans ? res.orphans.length : 0);

            var $orphansList = $('#orphans-list').empty();
            if (res.orphans && res.orphans.length) {
                res.orphans.forEach(function (url) {
                    $orphansList.append(
                        '<li class="list-group-item px-0 py-1 border-0 border-bottom">' +
                        '<i class="fas fa-unlink text-warning me-2 small"></i>' +
                        '<code class="small">' + $('<div>').text(url).html() + '</code>' +
                        '</li>'
                    );
                });
            } else {
                $orphansList.append('<li class="list-group-item text-muted small px-0">No hay páginas huérfanas</li>');
            }

            var $inboundList = $('#inbound-list').empty();
            var inbound = res.inbound_counts ?? {};
            var sorted  = Object.entries(inbound).sort(function (a, b) { return b[1] - a[1]; }).slice(0, 10);
            if (sorted.length) {
                sorted.forEach(function (entry) {
                    $inboundList.append(
                        '<li class="list-group-item px-0 py-1 border-0 border-bottom d-flex align-items-center gap-2">' +
                        '<span class="badge bg-primary-subtle text-primary flex-shrink-0">' + entry[1] + '</span>' +
                        '<code class="small text-truncate">' + $('<div>').text(entry[0]).html() + '</code>' +
                        '</li>'
                    );
                });
            } else {
                $inboundList.append('<li class="list-group-item text-muted small px-0">Sin datos de enlaces entrantes</li>');
            }

            $('#internal-results-section').show();
        }).fail(function () {
            toastr.error('Error al analizar los links internos.');
            $('#internal-progress-section').addClass('d-none');
            $('#internal-empty').removeClass('d-none');
        }).always(function () {
            $btn.prop('disabled', false).text('Analizar links internos');
        });
    });

});
</script>
@endpush
