@extends('layouts.managers')

@section('title', 'Alertas SEO')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Alertas SEO</h5>
                        <p class="mb-0 text-muted">Revisión de problemas detectados en el sitio</p>
                    </div>
                    @if($stats['unacknowledged'] > 0)
                        <div class="ms-auto">
                            <button type="button" class="btn btn-outline-secondary" id="acknowledge-all-btn">
                                Marcar todas como revisadas
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Sin revisar</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['unacknowledged']) }}</h4>
                                <span class="text-muted">Pendientes</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-danger-subtle h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2 text-danger">Criticas</h6>
                                <h4 class="mb-1 fw-bold text-danger">{{ number_format($stats['critical']) }}</h4>
                                <span class="text-muted">Alta prioridad</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-warning-subtle h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2 text-warning">Advertencias</h6>
                                <h4 class="mb-1 fw-bold text-warning">{{ number_format($stats['warning']) }}</h4>
                                <span class="text-muted">Media prioridad</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-info-subtle h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2 text-info">Informativas</h6>
                                <h4 class="mb-1 fw-bold text-info">{{ number_format($stats['info']) }}</h4>
                                <span class="text-muted">Baja prioridad</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.seo.alerts.index') }}" id="searchForm">

                    <input type="hidden" name="severity" id="filterSeverity" value="{{ $severity ?? '' }}">
                    <input type="hidden" name="status"   id="filterStatus"   value="{{ $status ?? '' }}">

                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por tipo o título..."
                                       value="{{ $search ?? '' }}">
                            </div>
                        </div>

                        @php
                            $activeFilters = (int)(($severity ?? '') !== '') + (int)(($status ?? '') !== '');
                        @endphp
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

            {{-- Tabla --}}
            <div class="card-body">
                @if($alerts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>Severidad</th>
                                    <th>Tipo</th>
                                    <th>Título</th>
                                    <th>Mensaje</th>
                                    <th>URL</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alerts as $alert)
                                    @php
                                        $severityMap = [
                                            'critical' => ['color' => 'danger',    'label' => 'Critica'],
                                            'warning'  => ['color' => 'warning',   'label' => 'Advertencia'],
                                            'info'     => ['color' => 'info',      'label' => 'Info'],
                                        ];
                                        $sev = $severityMap[$alert->severity] ?? ['color' => 'secondary', 'label' => $alert->severity];
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $sev['color'] }}-subtle text-{{ $sev['color'] }}">
                                                {{ $sev['label'] }}
                                            </span>
                                        </td>
                                        <td class="text-muted">{{ $alert->type }}</td>
                                        <td class="fw-semibold">{{ $alert->title }}</td>
                                        <td>
                                            <span class="text-muted" title="{{ $alert->message }}">
                                                {{ Str::limit($alert->message, 60) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($alert->url)
                                                <a href="{{ $alert->url }}" target="_blank" class="text-truncate d-block"
                                                   style="max-width:160px" title="{{ $alert->url }}">
                                                    {{ Str::limit($alert->url, 30) }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $alert->created_at->format('d/m/Y H:i') }}</div>
                                            <span class="text-muted">{{ $alert->created_at->diffForHumans() }}</span>
                                        </td>
                                        <td>
                                            @if($alert->acknowledged_at)
                                                <span class="badge bg-success-subtle text-success">Revisada</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(!$alert->acknowledged_at)
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                            data-bs-toggle="dropdown"
                                                            data-bs-boundary="viewport">
                                                        <i class="fas fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item acknowledge-btn" href="javascript:void(0)"
                                                               data-id="{{ $alert->id }}"
                                                               data-url="{{ route('manager.seo.alerts.acknowledge', $alert) }}">
                                                                Marcar como revisada
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item btn-delete" href="#"
                                                               data-url="{{ route('manager.seo.alerts.destroy', $alert) }}"
                                                               data-title="Eliminar alerta: {{ $alert->title }}">
                                                                Eliminar
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-50"></i>
                        <h5 class="fw-bold mb-2">Sin alertas pendientes</h5>
                        <p class="text-muted mb-4">
                            @if(($search ?? '') || ($severity ?? '') !== '' || ($status ?? '') !== '')
                                No se encontraron alertas con los filtros aplicados.
                            @else
                                No hay alertas SEO registradas en el sistema.
                            @endif
                        </p>
                        @if(($search ?? '') || ($severity ?? '') !== '' || ($status ?? '') !== '')
                            <a href="{{ route('manager.seo.alerts.index') }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if($alerts->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $alerts->firstItem() }}–{{ $alerts->lastItem() }} de {{ $alerts->total() }} alertas
                    </span>
                    {{ $alerts->appends(request()->input())->links() }}
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
                        <label class="form-label fw-semibold">Severidad</label>
                        <select id="modalSeverity" class="form-select">
                            <option value="">Todas</option>
                            <option value="critical"  {{ ($severity ?? '') === 'critical'  ? 'selected' : '' }}>Critica</option>
                            <option value="warning"   {{ ($severity ?? '') === 'warning'   ? 'selected' : '' }}>Advertencia</option>
                            <option value="info"      {{ ($severity ?? '') === 'info'      ? 'selected' : '' }}>Informativa</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Estado</label>
                        <select id="modalStatus" class="form-select">
                            <option value="">Todos</option>
                            <option value="pending"      {{ ($status ?? '') === 'pending'      ? 'selected' : '' }}>Pendiente</option>
                            <option value="acknowledged" {{ ($status ?? '') === 'acknowledged' ? 'selected' : '' }}>Revisada</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ route('manager.seo.alerts.index') }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm acknowledge all --}}
    <div class="modal fade" id="acknowledgeAllModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Marcar todas como revisadas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-warning mb-3">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <p class="mb-0">Se marcarán <strong>{{ $stats['unacknowledged'] }} alerta(s)</strong> como revisadas. Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="confirm-acknowledge-all">Confirmar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

@endsection

@push('scripts')
<script>
$(function () {

    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterSeverity').val($('#modalSeverity').val());
        $('#filterStatus').val($('#modalStatus').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Acknowledge single alert ─────────────────────────────────────────────
    $(document).on('click', '.acknowledge-btn', function (e) {
        e.preventDefault();
        var $item = $(this);
        var url   = $item.data('url');

        $item.closest('.dropdown-menu').find('.acknowledge-btn').addClass('disabled').text('Procesando...');

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                toastr.success(res.message || 'Alerta marcada como revisada.');
                setTimeout(function () { location.reload(); }, 600);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al procesar la alerta.');
                $item.removeClass('disabled').text('Marcar como revisada');
            }
        });
    });

    // ── Acknowledge all ──────────────────────────────────────────────────────
    $('#acknowledge-all-btn').on('click', function () {
        $('#acknowledgeAllModal').modal('show');
    });

    $('#confirm-acknowledge-all').on('click', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: '{{ route("manager.seo.alerts.acknowledge-all") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                $('#acknowledgeAllModal').modal('hide');
                toastr.success(res.message || 'Todas las alertas marcadas como revisadas.');
                setTimeout(function () { location.reload(); }, 600);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al procesar.');
                $btn.prop('disabled', false).text('Confirmar');
            }
        });
    });

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

});
</script>
@endpush
