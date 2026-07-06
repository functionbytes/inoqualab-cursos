@extends('layouts.managers')

@section('title', 'Temas')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Temas del curso</h5>
                        <p class="mb-0 text-muted">Gestiona los temas y su orden de aparición</p>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary btn-new-chapter"
                                data-bs-toggle="modal" data-bs-target="#chapter-modal">
                            Nuevo tema
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">

                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por título..."
                                       value="{{ $searchKey ?? '' }}">
                            </div>
                        </div>

                        @php
                            $activeFilters = (int)(($available ?? '') !== '');
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
                @if($chapters->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Título</th>
                                    <th class="text-center">Posición</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="chapters-sortable" data-reorder-url="{{ route('manager.courses.chapters.reorder') }}">
                                @foreach($chapters as $chapter)
                                    <tr data-id="{{ $chapter->id }}">
                                        <td>
                                            <div class="fw-semibold">
                                                <i class="fas fa-bars text-muted me-2 drag-handle" title="Arrastra para reordenar"></i>
                                                {{ Str::words($chapter->title, 8, '...') }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $chapter->position }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($chapter->available)
                                                <span class="badge bg-success-subtle text-success">Publico</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($chapter->updated_at)) }}</span>
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
                                                        <a class="dropdown-item btn-edit-chapter" href="#"
                                                           data-slack="{{ $chapter->slack }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.courses.chapters.destroy', $chapter->slack) }}"
                                                           data-title="Eliminar: {{ $chapter->title }}">
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
                        <i class="fas fa-layer-group fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay temas
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No hay temas que coincidan con los filtros aplicados.
                            @else
                                Crea el primer tema para este curso.
                            @endif
                        </p>
                        @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            <button type="button" class="btn btn-primary btn-new-chapter"
                                    data-bs-toggle="modal" data-bs-target="#chapter-modal">
                                Nuevo tema
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            @if($chapters->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $chapters->firstItem() }}–{{ $chapters->lastItem() }} de {{ $chapters->total() }} temas
                    </span>
                    {{ $chapters->appends(request()->input())->links() }}
                </div>
            @endif

        </div>
    </div>

    {{-- Filters modal --}}
    <div class="modal fade" id="filters-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filtros</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Estado</label>
                        <select id="modalAvailable" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ ($available ?? '') === '1' ? 'selected' : '' }}>Publico</option>
                            <option value="0" {{ ($available ?? '') === '0' ? 'selected' : '' }}>Oculto</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ Request::url() }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Crear / Editar tema --}}
    <div class="modal fade" id="chapter-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chapterModalTitle">Nuevo tema</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="chapterSlack" value="">
                    <p class="text-muted mb-3">
                        Completa los datos del tema. Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                    </p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="chapterTitle" maxlength="100" placeholder="Ingresar título">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Posición</label>
                            <input type="number" class="form-control" id="chapterPosition" min="1" placeholder="Ingresar posición">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                            <select class="select2 form-control" id="chapterAvailable">
                                @foreach($availables as $optId => $optLabel)
                                    <option value="{{ $optId }}">{{ $optLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <div class="quill-wrapper">
                                <div id="chapterDescription"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="btnSaveChapter" class="btn btn-primary w-100 mb-2">Guardar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

@endsection

@push('css')
<style>
    .drag-handle { cursor: grab; }
    #chapters-sortable tr.ui-sortable-helper { display: table; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
    .sortable-placeholder td { background: #eaf6fc; height: 48px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('managers/libs/jquery-ui/dist/jquery-ui.min.js') }}"></script>
<script>
$(function () {

    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterAvailable').val($('#modalAvailable').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

    // ── Reordenar por drag&drop ──────────────────────────────────────────────
    var $sortable = $('#chapters-sortable');
    if ($sortable.length && $.fn.sortable) {
        $sortable.sortable({
            handle: '.drag-handle',
            items: '> tr',
            axis: 'y',
            placeholder: 'sortable-placeholder',
            helper: function (e, tr) {
                var $originals = tr.children();
                var $helper = tr.clone();
                $helper.children().each(function (i) { $(this).width($originals.eq(i).width()); });
                return $helper;
            },
            update: function () {
                var ids = $sortable.find('> tr').map(function () { return $(this).data('id'); }).get();
                $.ajax({
                    url: $sortable.data('reorder-url'),
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: { ids: ids },
                    success: function (res) { toastr.success(res.message || 'Orden actualizado.'); },
                    error: function () { toastr.error('No se pudo guardar el orden.'); },
                });
            },
        });
    }

    // ── Crear / Editar tema (modal) ──────────────────────────────────────────
    var chapterQuill = null;
    var chapterMode = 'create';
    var nextPosition = {{ (int) $nextPosition }};

    function initChapterQuill() {
        if (chapterQuill) return;
        chapterQuill = new Quill('#chapterDescription', {
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['link'],
                    ['clean'],
                ],
            },
            placeholder: 'Escriba aquí...',
            theme: 'snow',
        });
    }

    // El script global (select2.init.js) ya auto-inicializa ".select2" en
    // documentReady SIN dropdownParent (el select vive oculto dentro del modal
    // en ese momento) — su dropdown terminaría flotando sobre <body> en vez del
    // modal. Por eso se destruye esa instancia y se re-crea con las opciones
    // correctas cada vez que el modal se abre.
    function initChapterSelect2() {
        var $select = $('#chapterAvailable');
        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        $select.select2({ dropdownParent: $('#chapter-modal'), width: '100%' });
    }

    function resetChapterForm() {
        $('#chapterSlack').val('');
        $('#chapterTitle').val('').removeClass('is-invalid');
        $('#chapterPosition').val(nextPosition);
        $('#chapterAvailable').val('1').trigger('change');
        if (chapterQuill) chapterQuill.setText('');
    }

    // Abrir en modo "crear"
    $('.btn-new-chapter').on('click', function () {
        chapterMode = 'create';
        $('#chapterModalTitle').text('Nuevo tema');
    });

    $('#chapter-modal').on('shown.bs.modal', function () {
        initChapterQuill();
        initChapterSelect2();
        if (chapterMode === 'create') {
            resetChapterForm();
        }
    });

    // Abrir en modo "editar": trae los datos vía AJAX y precarga el modal.
    $(document).on('click', '.btn-edit-chapter', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');

        $.getJSON("{{ url('panel/courses/chapters/edit') }}/" + slack, function (data) {
            chapterMode = 'edit';
            $('#chapterModalTitle').text('Editar tema');
            $('#chapter-modal').modal('show');

            var applyData = function () {
                initChapterQuill();
                initChapterSelect2();
                $('#chapterSlack').val(data.slack);
                $('#chapterTitle').val(data.title);
                $('#chapterPosition').val(data.position);
                $('#chapterAvailable').val(String(data.available)).trigger('change');
                chapterQuill.root.innerHTML = data.description || '';
            };

            if ($('#chapter-modal').hasClass('show')) {
                applyData();
            } else {
                $('#chapter-modal').one('shown.bs.modal', applyData);
            }
        }).fail(function () {
            toastr.error('No se pudo cargar el tema.');
        });
    });

    // Guardar (crear o actualizar según el modo)
    $('#btnSaveChapter').on('click', function () {
        var title = $.trim($('#chapterTitle').val());
        if (!title) {
            $('#chapterTitle').addClass('is-invalid');
            toastr.warning('El título es obligatorio.');
            return;
        }

        var description = chapterQuill ? chapterQuill.root.innerHTML.replace('<p><br></p>', '') : '';
        var isEdit = chapterMode === 'edit';
        var url = isEdit
            ? "{{ route('manager.courses.chapters.update') }}"
            : "{{ route('manager.courses.chapters.store') }}";

        var payload = {
            title: title,
            position: $('#chapterPosition').val(),
            description: description,
            available: $('#chapterAvailable').val(),
            course: "{{ $course->slack }}",
        };
        if (isEdit) payload.slack = $('#chapterSlack').val();

        var $btn = $(this).prop('disabled', true);
        var originalText = $btn.text();
        $btn.text('Guardando...');

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: payload,
            success: function (res) {
                $btn.prop('disabled', false).text(originalText);
                if (res.success) {
                    $('#chapter-modal').modal('hide');
                    toastr.success(res.message);
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    toastr.warning(res.message || 'No se pudo guardar.');
                }
            },
            error: function () {
                $btn.prop('disabled', false).text(originalText);
                toastr.error('Ocurrió un error al guardar el tema.');
            },
        });
    });

});
</script>
@endpush
