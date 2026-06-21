@extends('layouts.managers')

@section('title', 'Redirects SEO')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Redirects SEO</h5>
                        <p class="mb-0 text-muted">Gestiona las redirecciones HTTP del sitio</p>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRedirect">
                            Nuevo redirect
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search + Filters --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.seo.redirects.index') }}" id="searchForm">
                    <div class="row g-2">
                        <div class="col-12 col-lg">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por origen o destino..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-6 col-md-auto">
                            <select name="is_active" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="1" @selected(request('is_active') === '1')>Activos</option>
                                <option value="0" @selected(request('is_active') === '0')>Inactivos</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        @if(request('search') || (request('is_active') !== null && request('is_active') !== ''))
                            <div class="col-auto">
                                <a href="{{ route('manager.seo.redirects.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($redirects->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%">
                                        <input type="checkbox" id="select-all" class="form-check-input">
                                    </th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th>Codigo</th>
                                    <th>Tipo</th>
                                    <th>Hits</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($redirects as $redirect)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $redirect->id }}">
                                        </td>
                                        <td>
                                            <code class="small text-break">{{ $redirect->source_path }}</code>
                                        </td>
                                        <td>
                                            <code class="small text-break">{{ $redirect->target_path }}</code>
                                        </td>
                                        <td>
                                            <span class="badge {{ $redirect->status_code === 301 ? 'bg-primary' : 'bg-info' }}">
                                                {{ $redirect->status_code }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($redirect->is_regex)
                                                <span class="badge bg-warning text-dark">Regex</span>
                                            @elseif($redirect->is_wildcard)
                                                <span class="badge bg-secondary">Wildcard</span>
                                            @else
                                                <span class="badge bg-light text-dark border">Exacto</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ number_format($redirect->hits_count ?? 0) }}</span>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input toggle-active" type="checkbox"
                                                       data-id="{{ $redirect->id }}"
                                                       data-url="{{ route('manager.seo.redirects.toggle', $redirect->id) }}"
                                                       @checked($redirect->is_active)>
                                            </div>
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
                                                        <a class="dropdown-item btn-edit-redirect" href="#"
                                                           data-id="{{ $redirect->id }}"
                                                           data-source="{{ $redirect->source_path }}"
                                                           data-target="{{ $redirect->target_path }}"
                                                           data-code="{{ $redirect->status_code }}"
                                                           data-regex="{{ (int) $redirect->is_regex }}"
                                                           data-wildcard="{{ (int) $redirect->is_wildcard }}"
                                                           data-note="{{ $redirect->note }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete-redirect" href="#"
                                                           data-id="{{ $redirect->id }}"
                                                           data-url="{{ route('manager.seo.redirects.destroy', $redirect->id) }}">
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
                        <i class="fas fa-route fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">No hay redirects configurados</h5>
                        <p class="text-muted mb-4">Aun no hay redirects configurados</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRedirect">
                            Nuevo redirect
                        </button>
                    </div>
                @endif
            </div>

            {{-- Pagination --}}
            @if($redirects->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando {{ $redirects->firstItem() }} - {{ $redirects->lastItem() }}
                            de {{ $redirects->total() }}
                        </div>
                        <div>
                            {{ $redirects->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

    {{-- Modal crear/editar redirect --}}
    <div class="modal fade" id="modalRedirect" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRedirectTitle">Nuevo redirect</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formRedirect" onsubmit="return false">
                        <input type="hidden" id="redirect-id" name="redirect_id" value="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">URL de origen</label>
                            <input type="text" class="form-control" id="source_path" name="source_path"
                                   placeholder="/ruta-antigua">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">URL de destino</label>
                            <input type="text" class="form-control" id="target_path" name="target_path"
                                   placeholder="/nueva-ruta o https://ejemplo.com/pagina">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Codigo de estado</label>
                            <select class="form-select" id="status_code" name="status_code">
                                <option value="301">301 — Permanente</option>
                                <option value="302">302 — Temporal</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_regex" name="is_regex">
                                    <label class="form-check-label" for="is_regex">Es expresion regular</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_wildcard" name="is_wildcard">
                                    <label class="form-check-label" for="is_wildcard">Usa wildcard (*)</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Nota interna</label>
                            <textarea class="form-control" id="redirect-note" name="note" rows="2"
                                      placeholder="Motivo o contexto del redirect (opcional)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="btn-save-redirect">
                        Guardar redirect
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                        Cancelar
                    </button>
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
                        <strong><span data-bulk-count>0</span> redirect(s)</strong>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Accion</label>
                        <select id="bulk-action-select" class="form-select">
                            <option value="">Seleccionar accion...</option>
                            <option value="delete">Eliminar</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button id="bulk-apply-btn" type="button" class="btn btn-primary w-100 mb-2">Aplicar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
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
    var $modal    = $('#modalRedirect');

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
        var total   = $('.bulk-checkbox').length;
        var checked = $('.bulk-checkbox:checked').length;
        $('#select-all').prop('indeterminate', checked > 0 && checked < total);
        $('#select-all').prop('checked', checked === total);
        updateBulkToolbar();
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
        if (!ids.length) { toastr.warning('Selecciona al menos un redirect.'); return; }

        if (action === 'delete' && !confirm('¿Eliminar los ' + ids.length + ' redirect(s)?')) return;

        $('#bulk-apply-btn').prop('disabled', true).text('Procesando...');

        $.ajax({
            url: '{{ route('manager.seo.redirects.bulk-destroy') }}',
            method: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: JSON.stringify({ action: action, ids: ids }),
            success: function (res) {
                $('#bulk-modal').modal('hide');
                toastr.success(res.message ?? 'Redirects eliminados.');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al procesar.');
                $('#bulk-apply-btn').prop('disabled', false).text('Aplicar');
            }
        });
    });

    // ── Nuevo redirect — resetear modal ─────────────────────────────────────
    $('[data-bs-target="#modalRedirect"]').on('click', function () {
        resetModal();
    });

    // ── Editar redirect ─────────────────────────────────────────────────────
    $(document).on('click', '.btn-edit-redirect', function (e) {
        e.preventDefault();
        var $el = $(this);
        resetModal();
        $('#modalRedirectTitle').text('Editar redirect');
        $('#redirect-id').val($el.data('id'));
        $('#source_path').val($el.data('source'));
        $('#target_path').val($el.data('target'));
        $('#status_code').val($el.data('code'));
        $('#is_regex').prop('checked', $el.data('regex') == 1);
        $('#is_wildcard').prop('checked', $el.data('wildcard') == 1);
        $('#redirect-note').val($el.data('note'));
        $modal.modal('show');
    });

    function resetModal() {
        $('#modalRedirectTitle').text('Nuevo redirect');
        $('#formRedirect')[0].reset();
        $('#redirect-id').val('');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    // ── Guardar redirect ────────────────────────────────────────────────────
    $('#btn-save-redirect').on('click', function () {
        var id     = $('#redirect-id').val();
        var url    = id
            ? '{{ route('manager.seo.redirects.update', ':id') }}'.replace(':id', id)
            : '{{ route('manager.seo.redirects.store') }}';
        var method = id ? 'PUT' : 'POST';

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        var $btn = $(this).prop('disabled', true).text('Guardando...');

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: {
                _method:     method,
                source_path: $('#source_path').val(),
                target_path: $('#target_path').val(),
                status_code: $('#status_code').val(),
                is_regex:    $('#is_regex').is(':checked') ? 1 : 0,
                is_wildcard: $('#is_wildcard').is(':checked') ? 1 : 0,
                note:        $('#redirect-note').val(),
            },
            success: function (response) {
                toastr.success(response.message ?? 'Redirect guardado');
                $modal.modal('hide');
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        $('#' + field).addClass('is-invalid')
                            .next('.invalid-feedback').text(messages[0]);
                    });
                } else {
                    toastr.error('Error al guardar el redirect');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('Guardar redirect');
            }
        });
    });

    // ── Toggle estado ───────────────────────────────────────────────────────
    $(document).on('change', '.toggle-active', function () {
        var $toggle = $(this);
        $.ajax({
            url: $toggle.data('url'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                toastr.success(response.message ?? 'Estado actualizado');
            },
            error: function () {
                $toggle.prop('checked', !$toggle.is(':checked'));
                toastr.error('Error al cambiar el estado');
            }
        });
    });

    // ── Eliminar individual ─────────────────────────────────────────────────
    $(document).on('click', '.btn-delete-redirect', function (e) {
        e.preventDefault();
        $('#delete-form').attr('action', $(this).data('url'));
        $('#delete-modal').modal('show');
    });

    $('#delete-form').on('submit', function (e) {
        e.preventDefault();
        const url = $(this).attr('action');
        const $btn = $(this).find('[type=submit]');
        $btn.prop('disabled', true).text('Eliminando...');
        $.ajax({
            url: url,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                $('#delete-modal').modal('hide');
                if (res.success) {
                    toastr.success(res.message || 'Eliminado correctamente');
                    setTimeout(() => location.reload(), 800);
                } else {
                    toastr.error(res.message || 'Error al eliminar');
                    $btn.prop('disabled', false).text('Confirmar');
                }
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
