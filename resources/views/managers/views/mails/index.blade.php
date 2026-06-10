@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Correos entrantes'])

    {{-- Stat cards --}}
    @php
        $pendingDelta   = ($stats['pending_today'] ?? 0) - ($stats['pending_yesterday'] ?? 0);
        $processedDelta = ($stats['processed_today'] ?? 0) - ($stats['processed_yesterday'] ?? 0);
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('manager.mails.index', ['status' => 'pending_review']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="display-6 fw-bold text-warning mb-1">{{ $stats['pending_today'] }}</div>
                    <div class="small text-muted">Pendientes hoy</div>
                    <div class="small mt-1 {{ $pendingDelta > 0 ? 'text-danger' : ($pendingDelta < 0 ? 'text-success' : 'text-muted') }}">
                        <i class="fas {{ $pendingDelta > 0 ? 'fa-arrow-up' : ($pendingDelta < 0 ? 'fa-arrow-down' : 'fa-minus') }} me-1"></i>{{ $pendingDelta > 0 ? '+' . $pendingDelta : $pendingDelta }} vs ayer
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('manager.mails.index', ['status' => 'processed']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="display-6 fw-bold text-success mb-1">{{ $stats['processed_today'] }}</div>
                    <div class="small text-muted">Procesados hoy</div>
                    <div class="small mt-1 {{ $processedDelta > 0 ? 'text-success' : ($processedDelta < 0 ? 'text-danger' : 'text-muted') }}">
                        <i class="fas {{ $processedDelta > 0 ? 'fa-arrow-up' : ($processedDelta < 0 ? 'fa-arrow-down' : 'fa-minus') }} me-1"></i>{{ $processedDelta > 0 ? '+' . $processedDelta : $processedDelta }} vs ayer
                    </div>
                    @if(!is_null($stats['avg_minutes_today']))
                        <div class="small text-muted mt-1">
                            <i class="fas fa-clock me-1"></i>{{ $stats['avg_minutes_today'] }} min promedio
                        </div>
                    @endif
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('manager.mails.index', ['status' => 'failed']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="display-6 fw-bold text-danger mb-1">{{ $stats['failed_week'] }}</div>
                    <div class="small text-muted">Fallidos esta semana</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('manager.mails.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="display-6 fw-bold text-primary mb-1">{{ $stats['unresolved'] }}</div>
                    <div class="small text-muted">Sin resolver</div>
                </div>
            </a>
        </div>
    </div>

    {{-- Gráfica de actividad 7 días --}}
    <div class="card mb-3">
        <div class="card-header p-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0 fw-bold">Actividad — últimos 7 días</h6>
                <small class="text-muted">Correos recibidos, procesados y fallidos por día</small>
            </div>
        </div>
        <div class="card-body p-2" style="height:200px">
            <div id="mails-activity-chart" style="height:100%"></div>
        </div>
    </div>

    <div class="widget-content searchable-container list">

        {{-- Barra de filtros --}}
        <div class="card card-body">
            <form class="form-search" action="{{ route('manager.mails.index') }}" method="GET">
                <div class="row justify-content-between g-2">
                    <div class="col-auto flex-grow-1">
                        <div class="tt-search-box">
                            <div class="input-group">
                                <span class="position-absolute top-50 start-0 translate-middle-y ms-2">
                                    <i class="fas fa-search text-muted" style="font-size:13px"></i>
                                </span>
                                <input class="form-control ps-4 rounded-start w-100" type="text" name="search"
                                       placeholder="Buscar por remitente o asunto..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-auto" style="min-width:170px">
                        <select class="form-select select2" name="status">
                            <option value="">Todos los estados</option>
                            <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pendientes</option>
                            <option value="processed"      {{ request('status') === 'processed'      ? 'selected' : '' }}>Procesados</option>
                            <option value="failed"         {{ request('status') === 'failed'         ? 'selected' : '' }}>Fallidos</option>
                            <option value="ignored"        {{ request('status') === 'ignored'        ? 'selected' : '' }}>Ignorados</option>
                        </select>
                    </div>
                    <div class="col-auto" style="min-width:200px">
                        <select class="form-select" name="enterprise_id" id="enterprise-filter">
                            @if($selectedEnterprise)
                                <option value="{{ $selectedEnterprise->id }}" selected>{{ $selectedEnterprise->title }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-auto" style="min-width:155px">
                        <select class="form-select select2-conf" name="confidence">
                            <option value=""           {{ request('confidence') === ''       ? 'selected' : '' }}>Cualquier confianza</option>
                            <option value="high"       {{ request('confidence') === 'high'   ? 'selected' : '' }}>Alta (≥ 90%)</option>
                            <option value="medium"     {{ request('confidence') === 'medium' ? 'selected' : '' }}>Media (50–89%)</option>
                            <option value="low"        {{ request('confidence') === 'low'    ? 'selected' : '' }}>Baja (&lt; 50%)</option>
                        </select>
                    </div>
                    <div class="col-auto" style="min-width:220px">
                        <div class="input-group">
                            <input type="text" class="form-control daterange" id="daterange"
                                   placeholder="Rango de fechas" autocomplete="off"
                                   value="{{ (request('date_from') && request('date_to')) ? request('date_from') . ' - ' . request('date_to') : '' }}">
                            <input type="hidden" id="date_from" name="date_from" value="{{ request('date_from', '') }}">
                            <input type="hidden" id="date_to"   name="date_to"   value="{{ request('date_to', '') }}">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" title="Buscar">
                            <i class="fas fa-magnifying-glass"></i>
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('manager.mails.index') }}" class="btn btn-secondary" data-bs-toggle="tooltip" title="Limpiar filtros">
                            <i class="fas fa-xmark"></i>
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="#" id="export-btn" class="btn btn-outline-success"
                           data-bs-toggle="tooltip" title="Exportar CSV">
                            <i class="fas fa-file-csv"></i>
                        </a>
                    </div>
                    <div class="col-auto" style="min-width:190px">
                        <select class="form-select select2-assigned" name="assigned_to">
                            <option value=""         {{ request('assigned_to') === ''          ? 'selected' : '' }}>Todos los revisores</option>
                            <option value="me"       {{ request('assigned_to') === 'me'        ? 'selected' : '' }}>Mis correos</option>
                            <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Sin asignar</option>
                            @if($reviewers->isNotEmpty())
                                <optgroup label="Por revisor">
                                    @foreach($reviewers as $reviewer)
                                        <option value="{{ $reviewer->id }}"
                                            {{ (string) request('assigned_to') === (string) $reviewer->id ? 'selected' : '' }}>
                                            {{ trim($reviewer->firstname . ' ' . $reviewer->lastname) }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-warning" id="aliases-btn"
                                data-bs-toggle="tooltip" title="Alias faltantes (últimos 30 días)">
                            <i class="fas fa-key"></i>
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('manager.settings.mails') }}" class="btn btn-outline-secondary"
                           data-bs-toggle="tooltip" title="Configuración de correos">
                            <i class="fas fa-gear"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Bulk action toolbar --}}
        <div id="bulk-toolbar" class="card card-body d-none py-2 mb-0">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-semibold small text-primary">
                    <i class="fas fa-check-square me-1"></i>
                    <span id="bulk-count">0</span> seleccionados
                </span>
                <button type="button" id="bulk-reparse-btn" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-rotate me-1"></i> Re-analizar
                </button>
                <button type="button" id="bulk-discard-btn" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-ban me-1"></i> Descartar
                </button>
                <button type="button" id="bulk-cancel-btn" class="btn btn-light btn-sm ms-auto">
                    <i class="fas fa-xmark me-1"></i> Cancelar selección
                </button>
            </div>
        </div>

        {{-- Contador de estados --}}
        <div class="card card-body py-2">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                @php
                    $badgeMap = [
                        'pending_review' => ['label' => 'Pendientes', 'class' => 'bg-warning text-white'],
                        'processed'      => ['label' => 'Procesados', 'class' => 'bg-success text-white'],
                        'failed'         => ['label' => 'Fallidos',   'class' => 'bg-danger text-white'],
                        'ignored'        => ['label' => 'Ignorados',  'class' => 'bg-secondary text-white'],
                    ];
                @endphp
                <span class="text-muted small me-1">Totales:</span>
                @foreach($badgeMap as $key => $cfg)
                    @if($counts->has($key))
                        <span class="badge {{ $cfg['class'] }} rounded-3 py-1 px-2">
                            {{ $cfg['label'] }} {{ $counts->get($key) }}
                        </span>
                    @endif
                @endforeach
                @if($counts->isEmpty())
                    <span class="text-muted small">Sin registros</span>
                @endif
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card card-body">
            <div class="table-responsive">
                <table class="table search-table align-middle text-nowrap">
                    @php
                        $baseParams = array_filter(request()->only(['search','status','enterprise_id','date_from','date_to','confidence','per_page']));
                        $sortLink = function (string $col) use ($baseParams, $sort, $direction): string {
                            $newDir = ($sort === $col && $direction === 'desc') ? 'asc' : 'desc';
                            return route('manager.mails.index') . '?' . http_build_query(array_merge($baseParams, ['sort' => $col, 'direction' => $newDir]));
                        };
                        $sortIcon = function (string $col) use ($sort, $direction): string {
                            if ($sort !== $col) return '<i class="fas fa-sort ms-1 text-muted" style="font-size:10px"></i>';
                            return '<i class="fas fa-sort-' . ($direction === 'asc' ? 'up' : 'down') . ' ms-1 text-primary" style="font-size:10px"></i>';
                        };
                    @endphp
                    <thead class="header-item">
                        <tr>
                            <th class="px-2" style="width:30px">
                                <input type="checkbox" class="form-check-input" id="select-all-checkbox">
                            </th>
                            <th>Remitente</th>
                            <th>Asunto</th>
                            <th>Empresa</th>
                            <th>
                                <a href="{{ $sortLink('confidence_score') }}" class="text-dark text-decoration-none">
                                    Confianza {!! $sortIcon('confidence_score') !!}
                                </a>
                            </th>
                            <th>Estado</th>
                            <th>
                                <a href="{{ $sortLink('received_at') }}" class="text-dark text-decoration-none">
                                    Recibido {!! $sortIcon('received_at') !!}
                                </a>
                            </th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="mails-tbody">
                        @include('managers.views.mails._rows')
                    </tbody>
                </table>
            </div>
            <div class="result-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    @if($mails->total() > 0)
                        <span class="small text-muted">Mostrando {{ $mails->firstItem() }}–{{ $mails->lastItem() }} de {{ $mails->total() }}</span>
                    @endif
                    <select id="per-page-select" class="form-select form-select-sm" style="width:auto">
                        @foreach([15, 25, 50] as $pp)
                            <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }} por página</option>
                        @endforeach
                    </select>
                </div>
                <nav>{{ $mails->appends(request()->input())->links() }}</nav>
            </div>
        </div>

    </div>

    {{-- Modal alias faltantes --}}
    <div id="aliases-modal" class="modal fade">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-key me-2 text-warning"></i>Alias faltantes — últimos 30 días
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="aliases-modal-body">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin text-primary fs-3"></i>
                        <p class="text-muted mt-2 small">Analizando correos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal confirmación rápida --}}
    <div id="quick-confirm-modal" class="modal fade">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Confirmación rápida</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="text-success fs-1 mb-3"><i class="fas fa-circle-check"></i></div>
                    <p class="fw-semibold mb-1">¿Confirmar este correo?</p>
                    <p class="text-muted small fw-semibold mb-2" id="qc-enterprise-name"></p>
                    <p class="text-muted small mb-0">Se usarán los alias existentes para mapear los cursos automáticamente.</p>
                </div>
                <div class="modal-footer flex-column gap-2 pt-0">
                    <button type="button" id="quick-confirm-btn" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-circle-check me-1"></i> Confirmar y crear orden
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal preview --}}
    <div id="preview-modal" class="modal fade">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">
                            <i class="fas fa-envelope-open-text me-2 text-primary"></i>Vista previa
                        </h5>
                        <small class="text-muted" id="preview-from"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" id="preview-modal-body">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin text-primary fs-3"></i>
                        <p class="text-muted mt-2 small">Cargando correo...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="preview-open-link" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-expand-alt me-1"></i> Abrir revisión completa
                    </a>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal descartar --}}
    <div id="discard-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Descartar correo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-warning mb-2"><i class="fas fa-triangle-exclamation"></i></div>
                    <h5 class="mb-1">¿Descartar este correo?</h5>
                    <p class="text-muted small">Será marcado como ignorado y no generará orden.</p>
                    <div class="row justify-content-center mt-3">
                        <div class="col-6">
                            <button type="button" id="discard-confirm-btn" class="btn btn-danger w-100 mb-2">Confirmar</button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
<style>
@keyframes rowHighlight {
    0%   { background-color: #fff9c4; }
    100% { background-color: transparent; }
}
.row-new-highlight { animation: rowHighlight 4s ease-out forwards; }
.row-kb-focus { outline: 2px solid #008bce; outline-offset: -2px; background-color: #f0f8ff !important; }
</style>
@endpush

@push('scripts')
<script src="{{ url('managers/libs/daterangepicker/moment.min.js') }}"></script>
<script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}"></script>
<script>
$(document).ready(function () {

    var authUserId = {{ auth()->id() ?? 'null' }};

    // Select2 para filtro de estado
    $('select[name="status"]').select2({ minimumResultsForSearch: -1, placeholder: 'Todos los estados', allowClear: true });

    // Select2 para filtro de confianza
    $('.select2-conf').select2({ minimumResultsForSearch: -1, allowClear: true });

    // Select2 para filtro de asignación
    var reviewerCount = {{ $reviewers->count() }};
    $('.select2-assigned').select2({
        minimumResultsForSearch: reviewerCount > 3 ? 0 : -1,
        allowClear: true,
        placeholder: 'Todos los revisores'
    });

    // Gráfica de actividad (DevExpress)
    var chartData = @json($chartData->values());
    $('#mails-activity-chart').dxChart({
        dataSource: chartData,
        commonSeriesSettings: { argumentField: 'date', type: 'bar' },
        series: [
            { valueField: 'received', name: 'Recibidos', color: '#adb5bd' },
            { valueField: 'processed', name: 'Procesados', color: '#28a745' },
            { valueField: 'failed', name: 'Fallidos', color: '#dc3545' },
            { valueField: 'pending', name: 'Pendientes', color: '#ffc107' },
        ],
        argumentAxis: {
            label: {
                customizeText: function (e) {
                    var d = new Date(e.value + 'T00:00:00');
                    return d.toLocaleDateString('es-CO', { day: 'numeric', month: 'short' });
                }
            }
        },
        valueAxis: { allowDecimals: false },
        legend: { verticalAlignment: 'bottom', horizontalAlignment: 'center', itemTextPosition: 'right' },
        tooltip: { enabled: true, shared: true },
        barGroupPadding: 0.2,
    });

    // Select2 AJAX para filtro de empresa
    $('#enterprise-filter').select2({
        placeholder: 'Todas las empresas',
        allowClear: true,
        ajax: {
            url: '{{ route("manager.mails.enterprises") }}',
            dataType: 'json',
            delay: 300,
            data: function (params) { return { q: params.term || '' }; },
            processResults: function (data) { return { results: data }; },
            cache: true
        }
    });

    // Daterange picker
    if ($('#daterange').length) {
        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD', separator: ' - ',
                applyLabel: 'Aplicar', cancelLabel: 'Limpiar',
                daysOfWeek: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
                monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
                firstDay: 1
            }
        });
        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $('#date_from').val(picker.startDate.format('YYYY-MM-DD'));
            $('#date_to').val(picker.endDate.format('YYYY-MM-DD'));
        });
        $('#daterange').on('cancel.daterangepicker', function() {
            $(this).val(''); $('#date_from').val(''); $('#date_to').val('');
        });
    }

    // Per-page selector
    $('#per-page-select').on('change', function () {
        var params = new URLSearchParams(window.location.search);
        params.set('per_page', $(this).val());
        params.delete('page');
        window.location.href = '{{ route("manager.mails.index") }}?' + params.toString();
    });

    // Dropdown overflow fix
    $(document).on('shown.bs.dropdown', function(e) {
        $(e.target).closest('.table-responsive').css('overflow', 'visible');
    });
    $(document).on('hidden.bs.dropdown', function(e) {
        $(e.target).closest('.table-responsive').css('overflow', '');
    });

    // Auto-refresh en pending_review — detecta y resalta filas nuevas
    @if($status === 'pending_review')
    function getRowSlacks() {
        return $('#mails-tbody .mail-checkbox').map(function () { return $(this).val(); }).get();
    }
    setInterval(function () {
        var knownSlacks = getRowSlacks();
        $.ajax({
            url: location.href,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                $('#mails-tbody').html(html);
                // Resaltar filas nuevas
                var newSlacks = getRowSlacks();
                newSlacks.forEach(function (slack) {
                    if (knownSlacks.indexOf(slack) === -1) {
                        var $row = $('#mails-tbody').find('.mail-checkbox[value="' + slack + '"]').closest('tr');
                        $row.addClass('row-new-highlight');
                        setTimeout(function () { $row.removeClass('row-new-highlight'); }, 4000);
                    }
                });
            }
        });
    }, 30000);
    @endif

    // Navegación por teclado en el índice (↑↓ + Enter)
    var $focusedRow = null;
    function focusRow($row) {
        if ($focusedRow) $focusedRow.removeClass('row-kb-focus');
        $focusedRow = $row;
        if ($focusedRow) {
            $focusedRow.addClass('row-kb-focus');
            $focusedRow[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }
    $(document).on('keydown', function (e) {
        if ($(e.target).is('input, textarea, select')) return;
        var $rows = $('#mails-tbody tr.search-items');
        if (!$rows.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            var idx = $focusedRow ? $rows.index($focusedRow) : -1;
            focusRow($rows.eq(Math.min(idx + 1, $rows.length - 1)));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            var idx = $focusedRow ? $rows.index($focusedRow) : $rows.length;
            focusRow($rows.eq(Math.max(idx - 1, 0)));
        } else if (e.key === 'Enter' && $focusedRow) {
            e.preventDefault();
            var href = $focusedRow.find('a[href]').first().attr('href');
            if (href) window.location.href = href;
        }
    });

    // Confirmación rápida
    var qcSlack = null;
    $(document).on('click', '.quick-confirm-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        qcSlack = $(this).data('slack');
        $('#qc-enterprise-name').text($(this).data('enterprise'));
        $('#quick-confirm-modal').modal('show');
    });
    $('#quick-confirm-btn').on('click', function () {
        if (!qcSlack) return;
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
        var url = '{{ route("manager.mails.confirm", ":slack") }}'.replace(':slack', qcSlack);
        $.ajax({
            url: url, method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            contentType: 'application/json',
            data: JSON.stringify({ quick_confirm: true }),
            success: function (r) {
                $('#quick-confirm-modal').modal('hide');
                $btn.prop('disabled', false).html('<i class="fas fa-circle-check me-1"></i> Confirmar y crear orden');
                r.success ? toastr.success(r.message) : toastr.error(r.message);
                if (r.success) setTimeout(function () { location.reload(); }, 900);
            },
            error: function () {
                $('#quick-confirm-modal').modal('hide');
                $btn.prop('disabled', false).html('<i class="fas fa-circle-check me-1"></i> Confirmar y crear orden');
                toastr.error('Error al procesar la solicitud.');
            }
        });
    });

    // === Acciones en lote ===
    $('#select-all-checkbox').on('change', function () {
        $('.mail-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkToolbar();
    });
    $(document).on('change', '.mail-checkbox', function () {
        updateBulkToolbar();
        var total = $('.mail-checkbox').length;
        var checked = $('.mail-checkbox:checked').length;
        $('#select-all-checkbox').prop('indeterminate', checked > 0 && checked < total).prop('checked', checked === total && total > 0);
    });
    function updateBulkToolbar() {
        var count = $('.mail-checkbox:checked').length;
        $('#bulk-count').text(count);
        count > 0 ? $('#bulk-toolbar').removeClass('d-none') : $('#bulk-toolbar').addClass('d-none');
    }
    $('#bulk-cancel-btn').on('click', function () {
        $('.mail-checkbox, #select-all-checkbox').prop('checked', false).prop('indeterminate', false);
        $('#bulk-toolbar').addClass('d-none');
    });
    function doBulkAction(action) {
        var slacks = $('.mail-checkbox:checked').map(function () { return $(this).val(); }).get();
        if (!slacks.length) return;
        $.ajax({
            url: '{{ route("manager.mails.bulk-action") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            contentType: 'application/json',
            data: JSON.stringify({ action: action, slacks: slacks }),
            success: function (r) {
                r.success ? toastr.success(r.message) : toastr.error(r.message);
                if (r.success) setTimeout(function () { location.reload(); }, 900);
            },
            error: function () { toastr.error('Error al procesar la solicitud.'); }
        });
    }
    $('#bulk-reparse-btn').on('click', function () { doBulkAction('reparse'); });
    $('#bulk-discard-btn').on('click', function () { doBulkAction('discard'); });

    // === Reporte alias faltantes ===
    function renderAliasRow(type, value, count) {
        var safe = $('<span>').text(value).html();
        var badge = type === 'enterprise'
            ? '<span class="badge bg-danger">' + count + '</span>'
            : '<span class="badge bg-warning text-dark">' + count + '</span>';
        var assignBtn = '<button type="button" class="btn btn-outline-primary btn-sm assign-alias-btn" '
            + 'data-type="' + type + '" data-value="' + $('<span>').text(value).html() + '">'
            + '<i class="fas fa-plus me-1"></i>Asignar</button>';
        if (type === 'enterprise') {
            return '<tr><td><code>' + safe + '</code></td><td class="text-center">' + badge + '</td><td class="text-center">' + assignBtn + '</td></tr>';
        }
        return '<tr><td>' + safe + '</td><td class="text-center">' + badge + '</td><td class="text-center">' + assignBtn + '</td></tr>';
    }
    function buildAliasesHtml(data) {
        var html = '';
        if (data.enterprise_codes.length > 0) {
            html += '<h6 class="fw-bold mb-2"><i class="fas fa-building me-1 text-warning"></i>Códigos de empresa sin alias <span class="text-muted fw-normal small">(sin match en los últimos 30 días)</span></h6>';
            html += '<div class="table-responsive mb-4"><table class="table table-sm table-bordered"><thead><tr><th>Código recibido</th><th class="text-center" style="width:80px">Correos</th><th class="text-center" style="width:110px">Acción</th></tr></thead><tbody>';
            $.each(data.enterprise_codes, function (i, item) { html += renderAliasRow('enterprise', item.code, item.total); });
            html += '</tbody></table></div>';
        } else {
            html += '<div class="alert alert-success small mb-4"><i class="fas fa-check-circle me-1"></i>Todos los códigos de empresa tienen alias registrado.</div>';
        }
        if (data.course_texts.length > 0) {
            html += '<h6 class="fw-bold mb-2"><i class="fas fa-book me-1 text-warning"></i>Textos de curso sin alias</h6>';
            html += '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Texto recibido</th><th class="text-center" style="width:80px">Apariciones</th><th class="text-center" style="width:110px">Acción</th></tr></thead><tbody>';
            $.each(data.course_texts, function (i, item) { html += renderAliasRow('course', item.text, item.count); });
            html += '</tbody></table></div>';
        } else {
            html += '<div class="alert alert-success small"><i class="fas fa-check-circle me-1"></i>Todos los textos de curso tienen alias registrado.</div>';
        }
        if (data.enterprise_codes.length === 0 && data.course_texts.length === 0) {
            html = '<div class="alert alert-success text-center py-4"><i class="fas fa-circle-check fs-3 d-block mb-2"></i>No hay alias faltantes en los últimos 30 días.</div>';
        }
        return html;
    }
    $('#aliases-btn').on('click', function () {
        $('#aliases-modal-body').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-primary fs-3"></i><p class="text-muted mt-2 small">Analizando correos...</p></div>');
        $('#aliases-modal').modal('show');
        $.ajax({
            url: '{{ route("manager.mails.missing-aliases") }}',
            success: function (data) { $('#aliases-modal-body').html(buildAliasesHtml(data)); },
            error: function () { $('#aliases-modal-body').html('<div class="alert alert-danger">Error al cargar el reporte.</div>'); }
        });
    });

    // Asignar alias desde el modal
    $(document).on('click', '.assign-alias-btn', function () {
        var $btn = $(this);
        var type  = $btn.data('type');
        var value = $btn.data('value');
        var $row  = $btn.closest('tr');
        // Ya tiene formulario abierto?
        if ($row.next('.alias-form-row').length) { $row.next('.alias-form-row').remove(); return; }

        var isEnterprise = type === 'enterprise';
        var formHtml = '<tr class="alias-form-row bg-light"><td colspan="3" class="p-2">';
        formHtml += '<div class="d-flex gap-2 align-items-start flex-wrap">';
        if (isEnterprise) {
            formHtml += '<div class="flex-grow-1"><label class="form-label form-label-sm mb-1">Empresa destino</label>'
                + '<select class="form-select form-select-sm alias-enterprise-select" style="min-width:200px"><option></option></select></div>'
                + '<div><label class="form-label form-label-sm mb-1">Tipo de alias</label>'
                + '<select class="form-select form-select-sm alias-type-select">'
                + '<option value="TYPE_CODE">Código (ej: EMP01)</option>'
                + '<option value="TYPE_NAME">Nombre (ej: Empresa S.A.)</option>'
                + '</select></div>';
        } else {
            formHtml += '<div class="flex-grow-1"><label class="form-label form-label-sm mb-1">Curso destino</label>'
                + '<select class="form-select form-select-sm alias-course-select" style="min-width:200px"><option></option></select></div>';
        }
        formHtml += '<div class="d-flex flex-column justify-content-end" style="padding-top:22px">'
            + '<button type="button" class="btn btn-success btn-sm save-alias-btn" '
            + 'data-type="' + type + '" data-value="' + $('<span>').text(value).html() + '">'
            + '<i class="fas fa-check me-1"></i>Guardar</button></div>';
        formHtml += '</div></td></tr>';

        $row.after(formHtml);
        var $newRow = $row.next('.alias-form-row');

        if (isEnterprise) {
            $newRow.find('.alias-enterprise-select').select2({
                dropdownParent: $('#aliases-modal'),
                placeholder: 'Buscar empresa...',
                ajax: {
                    url: '{{ route("manager.mails.enterprises") }}',
                    dataType: 'json', delay: 300,
                    data: function (p) { return { q: p.term || '' }; },
                    processResults: function (d) { return { results: d }; },
                    cache: true
                }
            });
        } else {
            $newRow.find('.alias-course-select').select2({
                dropdownParent: $('#aliases-modal'),
                placeholder: 'Buscar curso...',
                ajax: {
                    url: '{{ route("manager.mails.courses") }}',
                    dataType: 'json', delay: 300,
                    data: function (p) { return { q: p.term || '' }; },
                    processResults: function (d) { return { results: d }; },
                    cache: true
                }
            });
        }
    });

    $(document).on('click', '.save-alias-btn', function () {
        var $btn  = $(this);
        var type  = $btn.data('type');
        var value = $btn.data('value');
        var $formRow = $btn.closest('.alias-form-row');
        var targetId, aliasType = null;

        if (type === 'enterprise') {
            targetId  = $formRow.find('.alias-enterprise-select').val();
            aliasType = $formRow.find('.alias-type-select').val();
            if (!targetId) { toastr.warning('Selecciona una empresa.'); return; }
        } else {
            targetId = $formRow.find('.alias-course-select').val();
            if (!targetId) { toastr.warning('Selecciona un curso.'); return; }
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $.ajax({
            url: '{{ route("manager.mails.create-alias") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            contentType: 'application/json',
            data: JSON.stringify({ type: type, value: value, target_id: targetId, alias_type: aliasType }),
            success: function (r) {
                if (r.success) {
                    toastr.success(r.message);
                    var $dataRow = $formRow.prev('tr');
                    $formRow.remove();
                    $dataRow.find('.assign-alias-btn').replaceWith('<span class="badge bg-success"><i class="fas fa-check me-1"></i>Asignado</span>');
                } else {
                    toastr.error(r.message);
                    $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Guardar');
                }
            },
            error: function () {
                toastr.error('Error al guardar el alias.');
                $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Guardar');
            }
        });
    });

    // === Export CSV ===
    $('#export-btn').on('click', function (e) {
        e.preventDefault();
        var params = new URLSearchParams(window.location.search);
        params.delete('page');
        window.location.href = '{{ route("manager.mails.export") }}?' + params.toString();
    });

    // === Preview modal ===
    $(document).on('click', '.preview-btn', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');
        var openUrl = '{{ route("manager.mails.show", ":slack") }}'.replace(':slack', slack);
        var previewUrl = '{{ route("manager.mails.preview", ":slack") }}'.replace(':slack', slack);

        $('#preview-from').text('');
        $('#preview-open-link').attr('href', openUrl);
        $('#preview-modal-body').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin text-primary fs-3"></i><p class="text-muted mt-2 small">Cargando correo...</p></div>');
        $('#preview-modal').modal('show');

        $.ajax({
            url: previewUrl,
            success: function (r) {
                if (!r.success) { $('#preview-modal-body').html('<div class="alert alert-danger m-3">' + $('<span>').text(r.message || 'Error al cargar.').html() + '</div>'); return; }
                $('#preview-from').text(r.from + ' — ' + r.received_at);

                var statusMap = { pending_review: ['bg-light-warning text-warning', 'Pendiente'], processed: ['bg-light-success text-success', 'Procesado'], failed: ['bg-light-danger text-danger', 'Fallido'], ignored: ['bg-light-secondary text-secondary', 'Ignorado'] };
                var sm = statusMap[r.status] || ['bg-light-secondary text-secondary', r.status];
                var body = '<div class="p-3 border-bottom d-flex flex-wrap gap-2 align-items-center">';
                body += '<span class="badge ' + sm[0] + ' rounded-3 py-1 px-2">' + sm[1] + '</span>';
                if (r.enterprise) { body += '<span class="badge bg-light-primary text-primary rounded-3 py-1 px-2"><i class="fas fa-building me-1"></i>' + $('<span>').text(r.enterprise).html() + '</span>'; }
                if (r.confidence_score !== null) {
                    var cs = r.confidence_score;
                    var csc = cs >= 90 ? 'bg-success' : (cs >= 50 ? 'bg-warning' : 'bg-danger');
                    body += '<span class="badge ' + csc + ' rounded-3 py-1 px-2">' + cs + '%</span>';
                }
                body += '</div>';
                body += '<div class="p-3"><h6 class="fw-semibold mb-1">' + $('<span>').text(r.subject).html() + '</h6>';
                body += '<pre class="bg-light rounded p-3 small" style="white-space:pre-wrap;max-height:300px;overflow-y:auto">' + $('<span>').text(r.raw_body || '').html() + '</pre></div>';
                if (r.enterprise_stats) {
                    var es = r.enterprise_stats;
                    var rateClass = es.success_rate >= 80 ? 'text-success' : (es.success_rate >= 50 ? 'text-warning' : 'text-danger');
                    body += '<div class="p-3 border-top bg-light"><h6 class="fw-semibold small text-muted mb-2">HISTORIAL DE LA EMPRESA</h6>';
                    body += '<div class="d-flex flex-wrap gap-3 small">';
                    body += '<span><strong>' + es.total + '</strong> correos totales</span>';
                    body += '<span class="text-success"><strong>' + es.processed + '</strong> procesados</span>';
                    body += '<span class="text-danger"><strong>' + es.failed + '</strong> fallidos</span>';
                    body += '<span class="' + rateClass + '"><strong>' + es.success_rate + '%</strong> tasa de éxito</span>';
                    body += '</div></div>';
                }
                if (r.error_log) {
                    body += '<div class="p-3 border-top"><h6 class="fw-semibold text-danger mb-2"><i class="fas fa-triangle-exclamation me-1"></i>Error log</h6>';
                    body += '<pre class="bg-light rounded p-3 small text-danger" style="white-space:pre-wrap;max-height:120px;overflow-y:auto">' + $('<span>').text(r.error_log).html() + '</pre></div>';
                }
                $('#preview-modal-body').html(body);
            },
            error: function () { $('#preview-modal-body').html('<div class="alert alert-danger m-3">Error al cargar la vista previa.</div>'); }
        });
    });

    // Descartar
    var currentSlack = null;
    $(document).on('click', '.discard-btn', function(e) {
        e.preventDefault();
        currentSlack = $(this).data('slack');
        $('#discard-modal').modal('show');
    });
    $('#discard-confirm-btn').on('click', function () {
        if (!currentSlack) return;
        var url = '{{ route("manager.mails.discard", ":slack") }}'.replace(':slack', currentSlack);
        $.ajax({
            url: url, method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(r) {
                $('#discard-modal').modal('hide');
                r.success ? toastr.success(r.message) : toastr.error(r.message);
                if (r.success) setTimeout(() => location.reload(), 900);
            },
            error: function() {
                $('#discard-modal').modal('hide');
                toastr.error('Error al procesar la solicitud.');
            }
        });
    });

    // === Asignarme desde índice ===
    $(document).on('click', '.assign-me-btn', function (e) {
        e.preventDefault();
        var $btn  = $(this);
        var slack = $btn.data('slack');
        var url   = '{{ route("manager.mails.assign", ":slack") }}'.replace(':slack', slack);
        $btn.prop('disabled', true);
        $.ajax({
            url: url, method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            contentType: 'application/json',
            data: JSON.stringify({ user_id: authUserId }),
            success: function (r) {
                r.success ? toastr.success(r.message) : toastr.error(r.message);
                if (r.success) setTimeout(function () { location.reload(); }, 700);
                else $btn.prop('disabled', false);
            },
            error: function () {
                toastr.error('Error al asignar el correo.');
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
