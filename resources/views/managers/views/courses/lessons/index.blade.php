@extends('layouts.managers')

@section('title', 'Clases')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Clases del curso</h5>
                        <p class="mb-0 text-muted">Gestiona las clases y su contenido</p>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary btn-new-lesson"
                                data-bs-toggle="modal" data-bs-target="#lesson-modal">
                            Nueva clase
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ Request::fullUrl() }}" id="searchForm">

                    <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">
                    <input type="hidden" name="chapter"   id="filterChapter"   value="{{ $chapter ?? '' }}">
                    <input type="hidden" name="type"      id="filterType"      value="{{ $type ?? '' }}">

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
                            $activeFilters = (int)(($available ?? '') !== '') + (int)(($chapter ?? '') !== '') + (int)(($type ?? '') !== '');
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
                @if($lessons->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center"><input type="checkbox" class="form-check-input" id="select-all"></th>
                                    <th>Título</th>
                                    <th class="text-center">Posición</th>
                                    <th>Módulo</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="lessons-sortable" data-reorder-url="{{ route('manager.courses.lessons.reorder') }}">
                                @foreach($lessons as $lesson)
                                    <tr data-id="{{ $lesson->id }}">
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $lesson->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">
                                                <i class="fas fa-bars text-muted me-2 drag-handle" title="Arrastra para reordenar"></i>
                                                {{ Str::words($lesson->title, 8, '...') }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $lesson->position }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $lesson->chapter->title }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $lesson->type->title }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($lesson->available)
                                                <span class="badge bg-success-subtle text-success">Público</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $lesson->updated_at->format('d/m/Y') }}</span>
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
                                                        <a class="dropdown-item btn-edit-lesson" href="#"
                                                           data-slack="{{ $lesson->slack }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.courses.lessons.destroy', $lesson->slack) }}"
                                                           data-title="Eliminar: {{ $lesson->title }}">
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
                        <i class="fas fa-play-circle fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if($searchKey || $activeFilters > 0)
                                No se encontraron resultados
                            @else
                                No hay clases
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($searchKey || $activeFilters > 0)
                                No hay clases que coincidan con los filtros aplicados.
                            @else
                                Crea la primera clase de este curso.
                            @endif
                        </p>
                        @if($searchKey || $activeFilters > 0)
                            <a href="{{ route('manager.courses.lessons', $course->slack) }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @else
                            <button type="button" class="btn btn-primary btn-new-lesson"
                                    data-bs-toggle="modal" data-bs-target="#lesson-modal">
                                Nueva clase
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            @if($lessons->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $lessons->firstItem() }}–{{ $lessons->lastItem() }} de {{ $lessons->total() }} clases
                    </span>
                    {{ $lessons->appends(request()->input())->links() }}
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
                        <label class="form-label fw-semibold">Estado</label>
                        <select id="modalAvailable" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ ($available ?? '') === '1' ? 'selected' : '' }}>Público</option>
                            <option value="0" {{ ($available ?? '') === '0' ? 'selected' : '' }}>Oculto</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Módulo</label>
                        <select id="modalChapter" class="form-select">
                            <option value="">Todos</option>
                            @foreach($chapters as $item)
                                <option value="{{ $item->id }}" {{ (isset($chapter) && $chapter == $item->id) ? 'selected' : '' }}>
                                    {{ $item->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Tipo</label>
                        <select id="modalType" class="form-select">
                            <option value="">Todos</option>
                            @foreach($types as $item)
                                <option value="{{ $item->id }}" {{ (isset($type) && $type == $item->id) ? 'selected' : '' }}>
                                    {{ $item->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ route('manager.courses.lessons', $course->slack) }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

    {{-- Bulk toolbar --}}
    <div id="bulk-toolbar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none bulk-toolbar-float">
        <button type="button" class="btn btn-primary shadow-lg px-4"
                data-bs-toggle="modal" data-bs-target="#bulk-modal">
            <span data-bulk-count>0</span> seleccionada(s) &mdash; Aplicar acción
        </button>
    </div>

    {{-- Bulk modal --}}
    <div class="modal fade" id="bulk-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Acción masiva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Se aplicará la acción sobre <strong><span data-bulk-count>0</span> clase(s)</strong>.
                    </p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Acción</label>
                        <select id="bulk-action-select" class="form-select">
                            <option value="">Seleccionar acción...</option>
                            <option value="publish">Publicar</option>
                            <option value="hide">Ocultar</option>
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

    {{-- Crear / Editar clase --}}
    <div class="modal fade" id="lesson-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <form id="formLessons" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="lessonModalTitle">Nueva clase</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="lessonSlack" name="slack" value="">
                        <input type="hidden" name="course" value="{{ $course->slack }}">
                        <textarea class="d-none" id="detail" name="detail"></textarea>

                        <p class="text-muted mb-3">
                            El tipo de contenido (video, audio, imagen, PDF, etc.) determina qué campos deberás completar.
                        </p>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" maxlength="100" placeholder="Ingresar título">
                                <label id="title-error" class="error d-none" for="title"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tema <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="chapter" name="chapter" data-placeholder="Selecciona un tema">
                                    @foreach($chapterOptions as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="chapter-error" class="error d-none" for="chapter"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="type" name="type" data-placeholder="Selecciona un tipo">
                                    @foreach($typeOptions as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="type-error" class="error d-none" for="type"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Posición <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="position" name="position" placeholder="Ingresar posición">
                                <label id="position-error" class="error d-none" for="position"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="available" name="available">
                                    @foreach($availables as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-md-6 d-none divAudiovisual">
                                <label class="form-label fw-semibold">Plataforma <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="platform" name="platform" data-placeholder="Selecciona la plataforma">
                                    <option value=""></option>
                                    <option value="youtube">YouTube</option>
                                    <option value="vimeo">Vimeo</option>
                                </select>
                                <label id="platform-error" class="error d-none" for="platform"></label>
                            </div>

                            <div class="col-md-6 d-none divAudiovisual">
                                <label class="form-label fw-semibold">Link <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="url" name="url" placeholder="Ej: https://www.youtube.com/watch?v=... o https://vimeo.com/...">
                                <small id="url-platform" class="d-block mt-1"></small>
                                <label id="url-error" class="error d-none" for="url"></label>
                            </div>

                            <div class="col-md-6 d-none divAudiovisual divAudio">
                                <label class="form-label fw-semibold">Duración <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="duration" name="duration" placeholder="Ingresar duración">
                                <label id="duration-error" class="error d-none" for="duration"></label>
                            </div>

                            <div class="col-md-6 d-none divAudio divFiles">
                                <label class="form-label fw-semibold">Archivo</label>
                                <input type="file" class="form-control" id="file" name="file">
                                <small id="current-file-hint" class="form-text text-muted d-none"></small>
                                <label id="file-error" class="error d-none" for="file"></label>
                            </div>

                            <div class="col-md-6 d-none divFiles">
                                <label class="form-label fw-semibold">Tamaño</label>
                                <input type="text" class="form-control" id="size" name="size" readonly placeholder="Se calcula automáticamente">
                                <small class="form-text text-muted">Se completa solo al subir el archivo, en MB</small>
                                <label id="size-error" class="error d-none" for="size"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Detalle</label>
                                <div class="quill-wrapper">
                                    <div id="details"></div>
                                </div>
                                <label id="detail-error" class="error d-none" for="detail"></label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer flex-column">
                        <button type="submit" class="btn btn-primary w-100 mb-2">Guardar</button>
                        <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('css')
<style>
    .drag-handle { cursor: grab; }
    #lessons-sortable tr.ui-sortable-helper { display: table; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
    .sortable-placeholder td { background: #eaf6fc; height: 48px; }
    .bulk-toolbar-float { z-index: 1050; }
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
        $('#filterChapter').val($('#modalChapter').val());
        $('#filterType').val($('#modalType').val());
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

    // ── Selección masiva ─────────────────────────────────────────────────────
    function refreshBulk() {
        var count = $('.bulk-checkbox:checked').length;
        $('[data-bulk-count]').text(count);
        count > 0 ? $('#bulk-toolbar').removeClass('d-none') : $('#bulk-toolbar').addClass('d-none');
    }
    function bulkIds() {
        return $('.bulk-checkbox:checked').map(function () { return $(this).val(); }).get();
    }
    $('#select-all').on('change', function () {
        $('.bulk-checkbox').prop('checked', $(this).prop('checked'));
        refreshBulk();
    });
    $(document).on('change', '.bulk-checkbox', function () {
        var total = $('.bulk-checkbox').length;
        var checked = $('.bulk-checkbox:checked').length;
        $('#select-all').prop('indeterminate', checked > 0 && checked < total);
        $('#select-all').prop('checked', checked === total);
        refreshBulk();
    });
    $('#bulk-modal').on('hide.bs.modal', function () {
        $('#bulk-action-select').val('');
        $('#btn-bulk-apply').prop('disabled', false).text('Aplicar');
    });
    $('#btn-bulk-apply').on('click', function () {
        var action = $('#bulk-action-select').val();
        if (! action) { toastr.warning('Selecciona una acción.'); return; }
        var ids = bulkIds();
        if (! ids.length) { toastr.warning('No hay clases seleccionadas.'); return; }

        $('#btn-bulk-apply').prop('disabled', true).text('Procesando...');
        $.ajax({
            url: '{{ route('manager.courses.lessons.bulk-action') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { action: action, ids: ids },
            success: function (res) {
                $('#bulk-modal').modal('hide');
                toastr.success(res.message);
                setTimeout(function () { location.reload(); }, 800);
            },
            error: function () {
                $('#btn-bulk-apply').prop('disabled', false).text('Aplicar');
                toastr.error('No se pudo aplicar la acción.');
            },
        });
    });

    // ── Reordenar por drag&drop ──────────────────────────────────────────────
    var $sortable = $('#lessons-sortable');
    if ($sortable.length && $.fn.sortable) {
        $sortable.sortable({
            handle: '.drag-handle',
            items: '> tr',
            axis: 'y',
            placeholder: 'sortable-placeholder',
            helper: function (e, tr) {
                // Fija el ancho de las celdas al arrastrar para que no colapsen.
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

    // ── Crear / Editar clase (modal) ─────────────────────────────────────────
    Dropzone.autoDiscover = false;

    var lessonQuill = null;
    var lessonMode = 'create';
    var nextPosition = {{ (int) $nextPosition }};

    function initLessonQuill() {
        if (lessonQuill) return;
        var toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'header': 1 }, { 'header': 2 }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'script': 'sub' }, { 'script': 'super' }],
            [{ 'indent': '-1' }, { 'indent': '+1' }],
            [{ 'direction': 'rtl' }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ['link', 'image', 'video', 'formula'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'font': [] }],
            [{ 'align': [] }],
            ['clean'],
        ];
        lessonQuill = new Quill('#details', {
            modules: { toolbar: toolbarOptions },
            placeholder: 'Escriba aquí...',
            theme: 'snow',
        });
    }

    // El script global (select2.init.js) ya auto-inicializa ".select2" en
    // documentReady SIN dropdownParent (el select vive oculto dentro del modal
    // en ese momento) — su dropdown terminaría flotando sobre <body> en vez del
    // modal. Por eso se destruye esa instancia y se re-crea con las opciones
    // correctas cada vez que el modal (o el campo condicional) se muestra.
    function initLessonSelect2(selector) {
        var $select = $(selector);
        if (!$select.length) return;
        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        $select.select2({ dropdownParent: $('#lesson-modal'), width: '100%', placeholder: $select.data('placeholder') });
    }

    function toggleVisibility(value) {
        $('.divFiles, .divAudiovisual, .divAudio').addClass('d-none');
        if (value == 1) { $('.divAudiovisual').removeClass('d-none'); initLessonSelect2('#platform'); }
        else if (value == 2) $('.divAudio').removeClass('d-none');
        else if (value == 3 || value == 4 || value == 5) $('.divFiles').removeClass('d-none');
    }

    function detectVideoPlatform() {
        var url = $('#url').val();
        var platform = $('#platform').val();
        var $label = $('#url-platform');
        if (!url) { $label.text('').attr('class', 'd-block mt-1'); return; }
        var yt = /^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/)|youtu\.be\/)[\w\-]+/.test(url);
        var vm = /^(https?:\/\/)?(www\.)?(vimeo\.com\/|player\.vimeo\.com\/video\/)[\d]+/.test(url);
        if (platform === 'youtube') {
            $label.text(yt ? '✔ Enlace de YouTube válido' : '⚠ El enlace no corresponde a YouTube')
                  .attr('class', 'd-block mt-1 fw-semibold ' + (yt ? 'text-success' : 'text-warning'));
        } else if (platform === 'vimeo') {
            $label.text(vm ? '✔ Enlace de Vimeo válido' : '⚠ El enlace no corresponde a Vimeo')
                  .attr('class', 'd-block mt-1 fw-semibold ' + (vm ? 'text-success' : 'text-warning'));
        } else {
            var ok = yt || vm;
            $label.text(ok ? (yt ? '✔ YouTube detectado' : '✔ Vimeo detectado') : '⚠ Debe ser un enlace de YouTube o Vimeo')
                  .attr('class', 'd-block mt-1 fw-semibold ' + (ok ? 'text-success' : 'text-warning'));
        }
    }

    $('#type').on('change', function () {
        toggleVisibility($(this).val());
        detectVideoPlatform();
    });
    $('#url, #platform').on('input change', function () { detectVideoPlatform(); });

    // Autocompleta el tamaño (MB) al elegir un archivo, requerido para imagen/zip/pdf.
    $('#file').on('change', function () {
        var file = this.files[0];
        if (file && ['3', '4', '5'].includes($('#type').val())) {
            $('#size').val((file.size / 1048576).toFixed(2));
        }
    });

    $.validator.addMethod('videoUrl', function (value) {
        if (!value) return true;
        var platform = $('#platform').val();
        var yt = /^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/)|youtu\.be\/)[\w\-]+/.test(value);
        var vm = /^(https?:\/\/)?(www\.)?(vimeo\.com\/|player\.vimeo\.com\/video\/)[\d]+/.test(value);
        if (platform === 'youtube') return yt;
        if (platform === 'vimeo') return vm;
        return yt || vm;
    }, function () {
        var p = $('#platform').val();
        if (p === 'youtube') return 'El enlace debe ser de YouTube';
        if (p === 'vimeo') return 'El enlace debe ser de Vimeo';
        return 'Ingresa un enlace válido de YouTube o Vimeo';
    });

    var lessonValidator = $('#formLessons').validate({
        ignore: '.ignore',
        rules: {
            title: { required: true, minlength: 3, maxlength: 100 },
            position: { required: true, number: true, minlength: 1, maxlength: 10 },
            chapter: { required: true },
            type: { required: true },
            available: { required: true },
            platform: { required: function () { return $('#type').val() == '1'; } },
            url: {
                required: function () { return $('#type').val() == '1'; },
                videoUrl: function () { return $('#type').val() == '1'; },
            },
            duration: { required: function () { return ['1', '2'].includes($('#type').val()); } },
            size: { required: function () { return ['3', '4', '5'].includes($('#type').val()); } },
            // El archivo solo es obligatorio al crear: al editar se conserva el existente
            // si no se selecciona uno nuevo (ver LessonsController::update()).
            file: {
                required: function () {
                    return lessonMode === 'create' && ['2', '3', '4', '5'].includes($('#type').val());
                },
            },
        },
        messages: {
            title: { required: 'El título es obligatorio.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 100 caracteres.' },
            position: { required: 'La posición es obligatoria.', number: 'Solo se pueden ingresar números.' },
            chapter: { required: 'Selecciona un tema.' },
            type: { required: 'Selecciona un tipo.' },
            available: { required: 'Selecciona un estado.' },
            platform: { required: 'Selecciona la plataforma del video.' },
            url: { required: 'El enlace es obligatorio.', videoUrl: 'Ingresa un enlace válido de YouTube o Vimeo.' },
            duration: { required: 'La duración es obligatoria.' },
            size: { required: 'El tamaño es obligatorio.' },
            file: { required: 'Selecciona un archivo.' },
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element).addClass('error').removeClass('d-none');
        },
        submitHandler: function (form) {
            $('#detail').val(lessonQuill ? lessonQuill.root.innerHTML.replace('<p><br></p>', '') : '');

            var formData = new FormData(form);
            var url = lessonMode === 'edit'
                ? "{{ route('manager.courses.lessons.update') }}"
                : "{{ route('manager.courses.lessons.store') }}";

            var $submitButton = $('#formLessons button[type="submit"]').prop('disabled', true);
            var submitOriginalText = $submitButton.text();
            $submitButton.text('Guardando...');

            $.ajax({
                url: url,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    $submitButton.prop('disabled', false).text(submitOriginalText);
                    if (response.success) {
                        $('#lesson-modal').modal('hide');
                        toastr.success(response.message);
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        toastr.warning(response.message || 'No se pudo guardar.');
                    }
                },
                error: function () {
                    $submitButton.prop('disabled', false).text(submitOriginalText);
                    toastr.error('Ocurrió un error al guardar la clase.');
                },
            });
        },
    });

    function resetLessonForm() {
        $('#formLessons')[0].reset();
        lessonValidator.resetForm();
        $('#formLessons .is-invalid').removeClass('is-invalid');
        $('#lessonSlack').val('');
        $('#current-file-hint').addClass('d-none').text('');
        $('#url-platform').text('').attr('class', 'd-block mt-1');
        $('#position').val(nextPosition);
        // .reset() no dispara 'change': select2 necesita el evento para refrescar su UI.
        $('#chapter, #type, #available').trigger('change');
        toggleVisibility('');
        if (lessonQuill) lessonQuill.setText('');
    }

    function populateLessonForm(data) {
        $('#lessonSlack').val(data.slack);
        $('#title').val(data.title);
        $('#chapter').val(data.chapter_id).trigger('change');
        $('#type').val(data.type_id).trigger('change');
        $('#position').val(data.position);
        $('#available').val(String(data.available)).trigger('change');
        $('#platform').val(data.platform || '').trigger('change');
        $('#url').val(data.url || '');
        $('#duration').val(data.duration || '');
        $('#size').val(data.size || '');
        lessonQuill.root.innerHTML = data.detail || '';

        toggleVisibility(data.type_id);
        detectVideoPlatform();

        if (data.has_file) {
            $('#current-file-hint').text('Archivo actual: ' + data.file_name).removeClass('d-none');
        } else {
            $('#current-file-hint').addClass('d-none').text('');
        }
    }

    // Abrir en modo "crear"
    $(document).on('click', '.btn-new-lesson', function () {
        lessonMode = 'create';
        $('#lessonModalTitle').text('Nueva clase');
    });

    $('#lesson-modal').on('shown.bs.modal', function () {
        initLessonQuill();
        initLessonSelect2('#chapter');
        initLessonSelect2('#type');
        initLessonSelect2('#available');
        if (lessonMode === 'create') resetLessonForm();
    });

    // Abrir en modo "editar": trae los datos vía AJAX y precarga el modal.
    $(document).on('click', '.btn-edit-lesson', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');

        $.getJSON("{{ url('panel/courses/lessons/edit') }}/" + slack, function (data) {
            lessonMode = 'edit';
            $('#lessonModalTitle').text('Editar clase');
            $('#lesson-modal').modal('show');

            var applyData = function () {
                initLessonQuill();
                initLessonSelect2('#chapter');
                initLessonSelect2('#type');
                initLessonSelect2('#available');
                populateLessonForm(data);
            };

            if ($('#lesson-modal').hasClass('show')) {
                applyData();
            } else {
                $('#lesson-modal').one('shown.bs.modal', applyData);
            }
        }).fail(function () {
            toastr.error('No se pudo cargar la clase.');
        });
    });

});
</script>
@endpush
