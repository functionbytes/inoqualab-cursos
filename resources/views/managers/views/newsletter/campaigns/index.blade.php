@extends('layouts.managers')

@section('title', 'Campañas de newsletter')

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Campañas de newsletter</h5>
                        <p class="small mb-0 text-muted">Envía newsletters a tus suscriptores activos</p>
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('manager.newsletter.index') }}" class="btn btn-outline-secondary">
                            Suscriptores
                        </a>
                        <a href="{{ route('manager.newsletter.campaigns.create') }}" class="btn btn-primary">
                            + Nueva campaña
                        </a>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format(array_sum($stats)) }}</h4>
                                <p class="text-muted">Campañas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Borradores</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['draft'] ?? 0) }}</h4>
                                <p class="text-muted">En edición</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Enviadas</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['sent'] ?? 0) }}</h4>
                                <p class="text-muted">Completadas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Con error</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['failed'] ?? 0) }}</h4>
                                <p class="text-muted">Fallidas</p>
                            </div>
                        </div>
                    </div>
                    @if(($stats['sending'] ?? 0) > 0)
                    <div class="col-6 col-md">
                        <div class="card bg-warning-subtle h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Enviando</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['sending']) }}</h4>
                                <p class="text-muted">En progreso</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $activeFilters = (int)(($status ?? '') !== '');
                    $statusLabels  = ['draft' => 'Borrador', 'sending' => 'Enviando', 'sent' => 'Enviada', 'failed' => 'Fallida'];

                    $filterChips = [];
                    if (($status ?? '') !== '') {
                        $filterChips[] = [
                            'label'     => 'Estado: ' . ($statusLabels[$status] ?? $status),
                            'clear_url' => route('manager.newsletter.campaigns.index', array_filter(['search' => $search ?? ''])),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('manager.newsletter.campaigns.index') }}" id="searchForm">
                    <input type="hidden" name="status" id="filterStatus" value="{{ $status ?? '' }}">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por nombre o asunto..."
                                       value="{{ $search ?? '' }}">
                            </div>
                        </div>
                        <button type="button" class="btn btn-dark flex-shrink-0 position-relative"
                                data-bs-toggle="modal" data-bs-target="#filters-modal"
                                title="Filtros avanzados">
                            <i class="fas fa-sliders"></i>
                            @if($activeFilters > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size:.65rem;">{{ $activeFilters }}</span>
                            @endif
                        </button>
                        @if($activeFilters > 0)
                            <a href="{{ route('manager.newsletter.campaigns.index', array_filter(['search' => $search ?? ''])) }}"
                               class="btn btn-dark flex-shrink-0" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    @include('managers.includes.filter-chips', ['chips' => $filterChips])
                </form>
            </div>

            {{-- Contenido: vacío o tabla --}}
            @if($campaigns->isEmpty())
                <div class="card-body">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-paper-plane fa-3x mb-3 d-block opacity-25"></i>
                        <h5 class="fw-bold mb-2">
                            @if($search || $status) No se encontraron campañas @else No hay campañas @endif
                        </h5>
                        <p class="mb-4">
                            @if($search || $status)
                                Ninguna campaña coincide con los filtros aplicados.
                            @else
                                Crea tu primera campaña de newsletter para empezar a enviar.
                            @endif
                        </p>
                        @if($search || $status)
                            <a href="{{ route('manager.newsletter.campaigns.index') }}" class="btn btn-outline-secondary">Ver todas</a>
                        @else
                            <a href="{{ route('manager.newsletter.campaigns.create') }}" class="btn btn-primary">Crear primera campaña</a>
                        @endif
                    </div>
                </div>
            @else
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Asunto</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Enviados</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($campaigns as $campaign)
                                <tr data-id="{{ $campaign->id }}">
                                    <td class="fw-medium">{{ $campaign->name }}</td>
                                    <td class="text-muted small text-truncate" title="{{ $campaign->subject }}">
                                        {{ $campaign->subject }}
                                    </td>
                                    <td class="text-center">
                                        @switch($campaign->status)
                                            @case('draft')
                                                <span class="badge bg-secondary-subtle text-secondary">Borrador</span>
                                                @break
                                            @case('sending')
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="fas fa-circle-notch fa-spin me-1"></i>Enviando
                                                </span>
                                                @break
                                            @case('sent')
                                                <span class="badge bg-success-subtle text-success">Enviada</span>
                                                @break
                                            @case('failed')
                                                <span class="badge bg-danger-subtle text-danger">Fallida</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="text-center small">
                                        @if($campaign->isSent())
                                            <span class="text-success fw-medium">{{ number_format($campaign->sent_count) }}</span>
                                            @if($campaign->failed_count > 0)
                                                <span class="text-muted"> / </span>
                                                <span class="text-danger">{{ number_format($campaign->failed_count) }} err.</span>
                                            @endif
                                            <br><p class="text-muted">de {{ number_format($campaign->recipients_count) }}</p>
                                        @elseif($campaign->isSending())
                                            <span class="text-warning">{{ number_format($campaign->recipients_count) }} dest.</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        @if($campaign->sent_at)
                                            Enviada {{ $campaign->sent_at->diffForHumans() }}
                                        @elseif($campaign->started_at)
                                            Iniciada {{ $campaign->started_at->diffForHumans() }}
                                        @else
                                            Creada {{ $campaign->created_at->diffForHumans() }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('manager.newsletter.campaigns.preview', $campaign) }}" target="_blank">
                                                        Vista previa
                                                    </a>
                                                </li>
                                                @if($campaign->isDraft())
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.newsletter.campaigns.edit', $campaign) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item btn-send" type="button"
                                                            data-id="{{ $campaign->id }}"
                                                            data-name="{{ $campaign->name }}"
                                                            data-url="{{ route('manager.newsletter.campaigns.send', $campaign) }}">
                                                            Enviar campaña
                                                        </button>
                                                    </li>
                                                @endif
                                                @if($campaign->isFailed())
                                                    <li>
                                                        <button class="dropdown-item btn-retry" type="button"
                                                            data-url="{{ route('manager.newsletter.campaigns.retry', $campaign) }}">
                                                            Reintentar
                                                        </button>
                                                    </li>
                                                @endif
                                                @if(! $campaign->isSending())
                                                    <li>
                                                        <button class="dropdown-item btn-duplicate" type="button"
                                                            data-url="{{ route('manager.newsletter.campaigns.duplicate', $campaign) }}">
                                                            Duplicar
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button class="dropdown-item btn-delete" type="button"
                                                            data-id="{{ $campaign->id }}"
                                                            data-url="{{ route('manager.newsletter.campaigns.destroy', $campaign) }}">
                                                            Eliminar
                                                        </button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($campaigns->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Mostrando {{ $campaigns->firstItem() }}–{{ $campaigns->lastItem() }} de {{ $campaigns->total() }} campañas
                        </div>
                        {{ $campaigns->links() }}
                    </div>
                </div>
            @endif

        </div>
    </div>

{{-- Modal: filtros avanzados --}}
<div class="modal fade" id="filters-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filtros avanzados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-0">
                    <label class="form-label fw-semibold">Estado</label>
                    <select id="modalStatus" class="form-select">
                        <option value="">Todos</option>
                        <option value="draft"   @selected(($status ?? '') === 'draft')>Borrador</option>
                        <option value="sending" @selected(($status ?? '') === 'sending')>Enviando</option>
                        <option value="sent"    @selected(($status ?? '') === 'sent')>Enviada</option>
                        <option value="failed"  @selected(($status ?? '') === 'failed')>Fallida</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer flex-column border-top-0 pt-0">
                <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">Aplicar filtros</button>
                <a href="{{ route('manager.newsletter.campaigns.index') }}" class="btn btn-outline-secondary w-100">Limpiar filtros</a>
            </div>
        </div>
    </div>
</div>

{{-- Modal confirmación de envío --}}
<div class="modal fade" id="sendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">¿Enviar la campaña <strong id="sendCampaignName"></strong>?</p>
                <p class="text-muted small mb-0">Esta acción no se puede deshacer. Se enviará a todos los suscriptores activos.</p>
            </div>
            <div class="modal-footer d-block">
                <button type="button" class="btn btn-danger w-100 mb-2" id="btnConfirmSend">
                    Sí, enviar ahora
                </button>
                <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    // ── Select2 en modal de filtros ───────────────────────────────────────────
    if (typeof $.fn.select2 !== 'undefined') {
        $('#modalStatus').select2({ allowClear: false, width: '100%', dropdownParent: $('#filters-modal') });
    }

    // ── Filtros avanzados ─────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterStatus').val($('#modalStatus').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    var sendUrl = null;

    // Abrir modal de envío
    $(document).on('click', '.btn-send', function () {
        sendUrl = $(this).data('url');
        $('#sendCampaignName').text($(this).data('name'));
        new bootstrap.Modal(document.getElementById('sendModal')).show();
    });

    // Confirmar envío
    $('#btnConfirmSend').on('click', function () {
        if (! sendUrl) return;
        var $btn = $(this).prop('disabled', true).text('Enviando...');

        $.ajax({
            url: sendUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('sendModal')).hide();
                toastr.success(res.message);
                setTimeout(function () { location.reload(); }, 1500);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error al enviar la campaña.');
                $btn.prop('disabled', false).text('Sí, enviar ahora');
            },
        });
    });

    // Duplicar campaña
    $(document).on('click', '.btn-duplicate', function () {
        $.ajax({
            url: $(this).data('url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                toastr.success(res.message);
                if (res.redirect) {
                    setTimeout(function () { window.location.href = res.redirect; }, 900);
                }
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error al duplicar.');
            },
        });
    });

    // Reintentar campaña fallida
    $(document).on('click', '.btn-retry', function () {
        $.ajax({
            url: $(this).data('url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                toastr.success(res.message);
                setTimeout(function () { location.reload(); }, 900);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error al reintentar.');
            },
        });
    });

    // Eliminar campaña
    $(document).on('click', '.btn-delete', function () {
        if (! confirm('¿Eliminar esta campaña? La acción no se puede deshacer.')) return;
        var $row = $(this).closest('tr');

        $.ajax({
            url: $(this).data('url'),
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                toastr.success(res.message);
                $row.fadeOut(400, function () { $(this).remove(); });
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Error al eliminar.');
            },
        });
    });

    @if($hasSending)
    setTimeout(function () {
        if (document.querySelectorAll('.modal.show').length === 0) {
            location.reload();
        }
    }, 10000);
    @endif
})();
</script>
@endpush
