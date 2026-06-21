@extends('layouts.managers')

@section('title', 'Plantillas SEO')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Plantillas SEO</h5>
                        <p class="mb-0 text-muted">Patrones reutilizables para títulos y descripciones meta</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('manager.seo.templates.create') }}" class="btn btn-primary">
                            Nueva plantilla
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
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                                <span class="text-muted">Plantillas configuradas</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Activas</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['active']) }}</h4>
                                <span class="text-muted">En uso</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.seo.templates.index') }}" id="searchForm">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por nombre..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('manager.seo.templates.index') }}" class="btn btn-outline-secondary flex-shrink-0" title="Limpiar filtros">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($templates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Nombre</th>
                                    <th>Tipo de modelo</th>
                                    <th>Patrón título</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Prioridad</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $template->id }}">
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ $template->name }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $template->model_type ? class_basename($template->model_type) : 'Global' }}
                                            </span>
                                        </td>
                                        <td>
                                            <code>{{ Str::limit($template->title_pattern ?? '—', 50) }}</code>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-switch d-flex justify-content-center">
                                                <input type="checkbox"
                                                       class="form-check-input toggle-active"
                                                       id="toggle-{{ $template->id }}"
                                                       data-id="{{ $template->id }}"
                                                       data-url="{{ route('manager.seo.templates.toggle-active', $template) }}"
                                                       {{ $template->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $template->priority }}</span>
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
                                                        <a class="dropdown-item" href="{{ route('manager.seo.templates.edit', $template) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item apply-btn" href="#"
                                                           data-id="{{ $template->id }}"
                                                           data-preview-url="{{ route('manager.seo.templates.preview', $template) }}"
                                                           data-apply-url="{{ route('manager.seo.templates.bulk-apply', $template) }}"
                                                           data-name="{{ $template->name }}">
                                                            Aplicar a metas
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.seo.templates.destroy', $template) }}"
                                                           data-title="Eliminar: {{ $template->name }}">
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
                        <i class="fas fa-file-code fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(request('search'))
                                No se encontraron resultados
                            @else
                                Sin plantillas
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(request('search'))
                                No hay plantillas que coincidan con la búsqueda.
                            @else
                                Crea tu primera plantilla SEO para comenzar.
                            @endif
                        </p>
                        @if(request('search'))
                            <a href="{{ route('manager.seo.templates.index') }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @else
                            <a href="{{ route('manager.seo.templates.create') }}" class="btn btn-primary">
                                Nueva plantilla
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if($templates->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $templates->firstItem() }}–{{ $templates->lastItem() }} de {{ $templates->total() }} plantillas
                    </span>
                    {{ $templates->appends(request()->input())->links() }}
                </div>
            @endif

        </div>
    </div>

    {{-- Apply to metas modal --}}
    <div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aplicar plantilla a metas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" id="apply-modal-body">
                    <div class="py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Calculando registros afectados...</p>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" class="btn btn-primary w-100 mb-2" id="confirm-apply-btn" disabled>Aplicar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
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
                        Se aplicará la acción sobre <strong><span data-bulk-count>0</span> plantilla(s)</strong>.
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
                    <button id="btn-bulk-apply" type="button" class="btn btn-primary w-100 mb-2">Aplicar</button>
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

    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif

    // ── Toggle active state ──────────────────────────────────────────────────
    $(document).on('change', '.toggle-active', function () {
        var $toggle = $(this);

        $.ajax({
            url: $toggle.data('url'),
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (res) {
                toastr.success(res.message ?? 'Estado actualizado.');
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al actualizar el estado.');
                $toggle.prop('checked', !$toggle.prop('checked'));
            }
        });
    });

    // ── Apply to metas ───────────────────────────────────────────────────────
    var currentApplyData = null;

    $(document).on('click', '.apply-btn', function (e) {
        e.preventDefault();
        var previewUrl = $(this).data('preview-url');
        var applyUrl   = $(this).data('apply-url');
        var templateId = $(this).data('id');

        currentApplyData = { applyUrl: applyUrl, templateId: templateId };

        $('#apply-modal-body').html(
            '<div class="py-3">' +
            '<div class="spinner-border text-primary" role="status"></div>' +
            '<p class="mt-2 text-muted">Calculando registros afectados...</p>' +
            '</div>'
        );
        $('#confirm-apply-btn').prop('disabled', true);
        $('#applyModal').modal('show');

        $.getJSON(previewUrl, function (res) {
            var count = res.affected_count ?? 0;
            $('#apply-modal-body').html(
                '<div class="display-4 text-primary mb-3"><i class="fas fa-layer-group"></i></div>' +
                '<p class="mb-1">Esta plantilla se aplicará a</p>' +
                '<h3 class="fw-bold mb-1">' + count + '</h3>' +
                '<p class="text-muted mb-0">registro(s) de meta SEO.</p>'
            );
            $('#confirm-apply-btn').prop('disabled', count === 0);
        }).fail(function () {
            $('#apply-modal-body').html('<p class="text-danger py-3">Error al cargar la previsualización.</p>');
        });
    });

    $('#confirm-apply-btn').on('click', function () {
        if (!currentApplyData) return;
        var $btn = $(this);
        $btn.prop('disabled', true).text('Aplicando...');

        $.ajax({
            url: currentApplyData.applyUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ template_id: currentApplyData.templateId }),
            success: function (res) {
                $('#applyModal').modal('hide');
                toastr.success(res.message ?? 'Plantilla aplicada correctamente.');
                currentApplyData = null;
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al aplicar la plantilla.');
                $btn.prop('disabled', false).text('Aplicar');
            }
        });
    });

    $('#applyModal').on('hidden.bs.modal', function () {
        currentApplyData = null;
        $('#confirm-apply-btn').prop('disabled', true).text('Aplicar');
    });

    // ── Bulk selection ───────────────────────────────────────────────────────
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
        if (!ids.length) { toastr.warning('Selecciona al menos una plantilla.'); return; }

        if (action === 'delete' && !confirm('¿Eliminar ' + ids.length + ' plantilla(s)?')) return;

        $('#btn-bulk-apply').prop('disabled', true).text('Procesando...');

        $.ajax({
            url: '{{ route('manager.seo.templates.bulk-action') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ action: action, ids: ids }),
            success: function (res) {
                $('#bulk-modal').modal('hide');
                toastr.success(res.message ?? ids.length + ' plantilla(s) procesadas.');
                setTimeout(function () { location.reload(); }, 700);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? 'Error al procesar.');
                $('#btn-bulk-apply').prop('disabled', false).text('Aplicar');
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
