@extends('layouts.managers')

@section('title', 'Reportes programados')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Reportes programados</h5>
                        <p class="mb-0 text-muted">Envios automaticos de reportes de analytics por email</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('manager.settings.analytics.schedules.create') }}" class="btn btn-primary">
                            Nuevo reporte
                        </a>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                                <span class="text-muted">Reportes configurados</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Activos</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['active']) }}</h4>
                                <span class="text-muted">Enviando reportes</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Inactivos</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['inactive']) }}</h4>
                                <span class="text-muted">Pausados</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Proximos 7 dias</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['pending']) }}</h4>
                                <span class="text-muted">Por enviar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search + Filters --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.settings.analytics.schedules.index') }}" id="searchForm">

                    {{-- Hidden inputs populated by the filters modal --}}
                    <input type="hidden" name="frequency" id="filterFrequency" value="{{ $frequency }}">
                    <input type="hidden" name="format"    id="filterFormat"    value="{{ $format }}">
                    <input type="hidden" name="status"    id="filterStatus"    value="{{ $status }}">

                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por nombre o email..."
                                       value="{{ $search }}">
                            </div>
                        </div>

                        @php $activeFilters = (int)($frequency !== '') + (int)($format !== '') + (int)($status !== ''); @endphp
                        <button type="button" class="btn btn-outline-secondary flex-shrink-0" title="Filtros"
                                data-bs-toggle="modal" data-bs-target="#filters-modal">
                            <i class="fas fa-sliders"></i>
                            @if($activeFilters > 0)
                                <span class="badge bg-primary ms-1">{{ $activeFilters }}</span>
                            @endif
                        </button>

                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($schedules->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%">
                                        <input type="checkbox" id="select-all" class="form-check-input">
                                    </th>
                                    <th>Nombre</th>
                                    <th>Frecuencia</th>
                                    <th>Email</th>
                                    <th>Formato</th>
                                    <th>Estado</th>
                                    <th>Ultimo envio</th>
                                    <th>Proximo envio</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedules as $schedule)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $schedule->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $schedule->name }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $freqLabels = [
                                                    'daily'   => 'Diario',
                                                    'weekly'  => 'Semanal',
                                                    'monthly' => 'Mensual',
                                                ];
                                            @endphp
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $freqLabels[$schedule->frequency] ?? $schedule->frequency }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $schedule->email }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary text-uppercase">
                                                {{ $schedule->format }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($schedule->is_active)
                                                <span class="badge bg-success-subtle text-success">Activo</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->last_sent_at)
                                                <div>{{ $schedule->last_sent_at->format('d/m/Y H:i') }}</div>
                                                <span class="text-muted">{{ $schedule->last_sent_at->diffForHumans() }}</span>
                                            @else
                                                <span class="text-muted">Nunca</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->next_run_at)
                                                <div>{{ $schedule->next_run_at->format('d/m/Y H:i') }}</div>
                                                <span class="text-muted">{{ $schedule->next_run_at->diffForHumans() }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.settings.analytics.schedules.edit', $schedule) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-btn" href="#"
                                                           data-url="{{ route('manager.settings.analytics.schedules.toggle', $schedule) }}"
                                                           data-active="{{ $schedule->is_active ? '1' : '0' }}">
                                                            {{ $schedule->is_active ? 'Desactivar' : 'Activar' }}
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item delete-btn" href="#"
                                                           data-url="{{ route('manager.settings.analytics.schedules.destroy', $schedule) }}"
                                                           data-title="Eliminar {{ $schedule->name }}">
                                                            Eliminar
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-alt fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if($search)
                                No se encontraron resultados
                            @else
                                No hay reportes programados
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($search)
                                No hay resultados para "{{ $search }}"
                            @else
                                Aun no hay reportes configurados
                            @endif
                        </p>
                        @if($search)
                            <a href="{{ route('manager.settings.analytics.schedules.index') }}"
                               class="btn btn-secondary">Limpiar busqueda</a>
                        @else
                            <a href="{{ route('manager.settings.analytics.schedules.create') }}"
                               class="btn btn-primary">
                                Nuevo reporte
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Pagination --}}
            @if($schedules->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando {{ $schedules->firstItem() }} - {{ $schedules->lastItem() }}
                            de {{ $schedules->total() }}
                        </div>
                        <div>
                            {{ $schedules->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

    {{-- Filters modal --}}
    <div class="modal fade" id="filters-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filtros avanzados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Frecuencia</label>
                        <select id="modalFrequency" class="form-select">
                            <option value="">Todos</option>
                            <option value="daily"   {{ $frequency === 'daily'   ? 'selected' : '' }}>Diario</option>
                            <option value="weekly"  {{ $frequency === 'weekly'  ? 'selected' : '' }}>Semanal</option>
                            <option value="monthly" {{ $frequency === 'monthly' ? 'selected' : '' }}>Mensual</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Formato</label>
                        <select id="modalFormat" class="form-select">
                            <option value="">Todos</option>
                            <option value="pdf"   {{ $format === 'pdf'   ? 'selected' : '' }}>PDF</option>
                            <option value="excel" {{ $format === 'excel' ? 'selected' : '' }}>Excel</option>
                            <option value="csv"   {{ $format === 'csv'   ? 'selected' : '' }}>CSV</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Estado</label>
                        <select id="modalStatus" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ $status === '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ route('manager.settings.analytics.schedules.index') }}"
                       class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk toolbar --}}
    <div id="bulk-toolbar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none bulk-toolbar-float">
        <button type="button" class="btn btn-primary shadow-lg px-4"
                data-bs-toggle="modal" data-bs-target="#bulk-modal">
            <span data-bulk-count>0</span> seleccionado(s) &mdash; Aplicar accion
        </button>
    </div>

    {{-- Bulk modal --}}
    <div class="modal fade" id="bulk-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Accion masiva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Se aplicara la accion sobre
                        <strong><span data-bulk-count>0</span> reporte(s)</strong>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Accion</label>
                        <select id="bulk-action-select" class="form-select">
                            <option value="">Seleccionar accion...</option>
                            <option value="activate">Activar</option>
                            <option value="deactivate">Desactivar</option>
                            <option value="delete">Eliminar</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button id="bulk-apply-btn" type="button" class="btn btn-primary w-100 mb-2">
                        Aplicar
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete modal --}}
    <div id="delete-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <form id="delete-form" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar eliminacion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="display-4 text-warning mb-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h5 class="fw-bold mb-2" id="delete-modal-title">¿Estas seguro?</h5>
                        <p class="text-muted">Esta accion no se puede deshacer.</p>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Confirmar eliminacion</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('css')
<style>.bulk-toolbar-float { z-index: 1050; }</style>
@endpush

@push('scripts')
<script>
$(function () {

    // ── Filters modal ───────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterFrequency').val($('#modalFrequency').val());
        $('#filterFormat').val($('#modalFormat').val());
        $('#filterStatus').val($('#modalStatus').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Bulk selection ──────────────────────────────────────────────────────
    function updateBulkToolbar() {
        var count = $('.bulk-checkbox:checked').length;
        $('[data-bulk-count]').text(count);
        count > 0 ? $('#bulk-toolbar').removeClass('d-none') : $('#bulk-toolbar').addClass('d-none');
    }

    function getSelectedIds() {
        return $('.bulk-checkbox:checked').map(function () { return $(this).val(); }).get();
    }

    $('#select-all').on('change', function () {
        $('.bulk-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkToolbar();
    });

    $(document).on('change', '.bulk-checkbox', function () {
        var total = $('.bulk-checkbox').length;
        var checked = $('.bulk-checkbox:checked').length;
        $('#select-all').prop('indeterminate', checked > 0 && checked < total);
        $('#select-all').prop('checked', checked === total);
        updateBulkToolbar();
    });

    // ── Toggle activo/inactivo ──────────────────────────────────────────────
    $(document).on('click', '.toggle-btn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $.post($btn.data('url'), { _token: $('meta[name="csrf-token"]').attr('content') })
            .done(function (res) {
                toastr.success(res.message || 'Estado actualizado.');
                setTimeout(function () { location.reload(); }, 800);
            })
            .fail(function () {
                toastr.error('Error al cambiar el estado.');
            });
    });

    // ── Delete modal ────────────────────────────────────────────────────────
    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        $('#delete-modal-title').text($(this).data('title'));
        $('#delete-form').attr('action', $(this).data('url'));
        $('#delete-modal').modal('show');
    });

    // ── Bulk modal ──────────────────────────────────────────────────────────
    $('#bulk-modal').on('hide.bs.modal', function () {
        $('#bulk-action-select').val('');
        $('#bulk-apply-btn').prop('disabled', false).text('Aplicar');
    });

    $('#bulk-apply-btn').on('click', function () {
        var action = $('#bulk-action-select').val();
        var ids    = getSelectedIds();

        if (!action) { toastr.warning('Selecciona una accion.'); return; }
        if (!ids.length) { toastr.warning('Selecciona al menos un reporte.'); return; }

        if (action === 'delete' && !confirm('¿Eliminar los ' + ids.length + ' reporte(s)?')) return;

        $('#bulk-apply-btn').prop('disabled', true).text('Procesando...');

        $.ajax({
            url: '{{ route("manager.settings.analytics.schedules.bulk-action") }}',
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: JSON.stringify({ action: action, ids: ids }),
            success: function (res) {
                $('#bulk-modal').modal('hide');
                toastr.success(res.message || ids.length + ' reporte(s) procesados.');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al procesar.');
                $('#bulk-apply-btn').prop('disabled', false).text('Aplicar');
            }
        });
    });

});
</script>
@endpush
