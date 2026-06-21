@extends('layouts.managers')

@section('title', 'URLs estáticas del sitemap')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">URLs estáticas del sitemap</h5>
                        <p class="mb-0 text-muted">Gestiona las URLs estáticas que se incluirán en el sitemap XML</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('manager.seo.static-urls.create') }}" class="btn btn-primary">
                            Nueva URL
                        </a>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($total) }}</h4>
                                <span class="text-muted">URLs configuradas</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Activas</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($totalActive) }}</h4>
                                <span class="text-muted">Incluidas en el sitemap</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Inactivas</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($total - $totalActive) }}</h4>
                                <span class="text-muted">Excluidas del sitemap</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.seo.static-urls.index') }}">
                    <div class="row g-2">
                        <div class="col-12 col-lg">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por URL o notas..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-6 col-md-auto">
                            <select name="status" class="form-select">
                                <option value="">Estado</option>
                                <option value="active" @selected(request('status') === 'active')>Activas</option>
                                <option value="inactive" @selected(request('status') === 'inactive')>Inactivas</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                        </div>
                        @if(request('search') || request('status'))
                            <div class="col-auto">
                                <a href="{{ route('manager.seo.static-urls.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($staticUrls->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>URL</th>
                                    <th class="text-center">Prioridad</th>
                                    <th class="text-center">Frecuencia</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staticUrls as $staticUrl)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $staticUrl->id }}">
                                        </td>
                                        <td>
                                            <code class="text-primary">{{ $staticUrl->url }}</code>
                                            @if($staticUrl->notes)
                                                <br><p class="text-muted">{{ $staticUrl->notes }}</p>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ number_format($staticUrl->priority, 1) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $staticUrl->changefreq }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($staticUrl->is_active)
                                                <span class="badge bg-success-subtle text-success">Activa</span>
                                            @else
                                                <span class="badge bg-light text-dark border">Inactiva</span>
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
                                                        <a class="dropdown-item" href="{{ route('manager.seo.static-urls.edit', $staticUrl) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item toggle-active" href="javascript:void(0)"
                                                           data-url="{{ route('manager.seo.static-urls.toggle', $staticUrl) }}">
                                                            {{ $staticUrl->is_active ? 'Desactivar' : 'Activar' }}
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.seo.static-urls.destroy', $staticUrl) }}"
                                                           data-title="Eliminar: {{ $staticUrl->url }}">
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
                        <i class="fas fa-link fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">No hay URLs estáticas configuradas</h5>
                        <p class="text-muted mb-4">Comienza agregando tu primera URL estática</p>
                        <a href="{{ route('manager.seo.static-urls.create') }}" class="btn btn-primary">
                            Agregar primera URL
                        </a>
                    </div>
                @endif
            </div>

            @if($staticUrls->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <p class="text-muted">
                        Mostrando {{ $staticUrls->firstItem() }}–{{ $staticUrls->lastItem() }} de {{ $staticUrls->total() }} registros
                    </p>
                    {{ $staticUrls->appends(request()->input())->links() }}
                </div>
            @endif

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
                        Se aplicará la acción sobre <strong><span data-bulk-count>0</span> URL(s)</strong>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Accion</label>
                        <select id="bulk-action-select" class="form-select">
                            <option value="">Seleccionar acción...</option>
                            <option value="activate">Activar</option>
                            <option value="deactivate">Desactivar</option>
                            <option value="delete">Eliminar</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button id="btn-bulk-apply" type="button" class="btn btn-primary w-100 mb-2">
                        Aplicar
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

@endsection

@push('css')
<style>.bulk-toolbar-float { z-index: 1050; }</style>
@endpush

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

    // ── Bulk selection ────────────────────────────────────────────────────────
    function updateBulkToolbar() {
        var count = $('.bulk-checkbox:checked').length;
        $('[data-bulk-count]').text(count);
        count > 0 ? $('#bulk-toolbar').removeClass('d-none') : $('#bulk-toolbar').addClass('d-none');
    }

    function getChecked() {
        return $('.bulk-checkbox:checked').map(function () { return $(this).val(); }).get();
    }

    $('#select-all').on('change', function () {
        $('.bulk-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkToolbar();
    });

    $(document).on('change', '.bulk-checkbox', function () {
        var total   = $('.bulk-checkbox').length;
        var checked = $('.bulk-checkbox:checked').length;
        $('#select-all').prop('indeterminate', checked > 0 && checked < total);
        $('#select-all').prop('checked', checked === total);
        updateBulkToolbar();
    });

    $('#bulk-modal').on('hide.bs.modal', function () {
        $('#bulk-action-select').val('');
        $('#btn-bulk-apply').prop('disabled', false).text('Aplicar');
    });

    $('#btn-bulk-apply').on('click', function () {
        var action = $('#bulk-action-select').val();
        var ids    = getChecked();

        if (!action) { toastr.warning('Selecciona una acción.'); return; }
        if (!ids.length) { toastr.warning('Selecciona al menos una URL.'); return; }

        $('#btn-bulk-apply').prop('disabled', true).text('Procesando...');

        $.ajax({
            url: '{{ route('manager.seo.static-urls.bulk-action') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ action: action, ids: ids }),
            success: function (res) {
                $('#bulk-modal').modal('hide');
                toastr.success(res.message ?? 'Acción aplicada.');
                setTimeout(function () { location.reload(); }, 700);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al procesar.');
                $('#btn-bulk-apply').prop('disabled', false).text('Aplicar');
            }
        });
    });

    // ── Toggle activo vía AJAX ────────────────────────────────────────────────
    $(document).on('click', '.toggle-active', function (e) {
        e.preventDefault();
        var url = $(this).data('url');

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { _method: 'PATCH' },
            success: function (res) {
                toastr.success(res.message ?? 'Estado actualizado.');
                setTimeout(function () { location.reload(); }, 600);
            },
            error: function () {
                toastr.error('Error al cambiar el estado.');
            }
        });
    });

    // ── Eliminar vía modal de confirmación ────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

    $('#delete-form').on('submit', function (e) {
        e.preventDefault();
        var url = $(this).attr('action');
        var $btn = $(this).find('[type=submit]');
        $btn.prop('disabled', true).text('Eliminando...');
        $.ajax({
            url: url,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                $('#delete-modal').modal('hide');
                toastr.success(res.message || 'Eliminado correctamente');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function () {
                toastr.error('Error al eliminar');
                $btn.prop('disabled', false).text('Confirmar');
            }
        });
    });

});
</script>

@endpush
