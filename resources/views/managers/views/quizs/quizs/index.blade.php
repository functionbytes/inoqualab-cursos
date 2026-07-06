@extends('layouts.managers')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Quizs del curso</h5>
                        <p class="mb-0 text-muted">Gestiona los quizs y sus preguntas</p>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary btn-new-quiz"
                                data-bs-toggle="modal" data-bs-target="#quiz-modal">
                            Nuevo quiz
                        </button>
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.courses.quiz', $course->slack) }}" id="searchForm">

                    <input type="hidden" name="lesson"    id="filterLesson"    value="{{ $lesson ?? '' }}">
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
                            $activeFilters = (int)(($lesson ?? '') !== '') + (int)(($available ?? '') !== '');
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
                @if($quizs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Título</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quizs as $quiz)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ Str::upper(Str::lower($quiz->title)) }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($quiz->available)
                                                <span class="badge bg-success-subtle text-success">Publico</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($quiz->updated_at)) }}</span>
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
                                                           href="{{ route('manager.courses.quiz.questions', $quiz->slack) }}">
                                                            Preguntas
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item btn-edit-quiz" href="#"
                                                           data-slack="{{ $quiz->slack }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.courses.quiz.destroy', $quiz->slack) }}"
                                                           data-title="Eliminar: {{ $quiz->title }}">
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
                        <i class="fas fa-question-circle fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if($searchKey || ($lesson ?? '') !== '' || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay quizs
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($searchKey || ($lesson ?? '') !== '' || ($available ?? '') !== '')
                                No hay quizs que coincidan con los filtros aplicados.
                            @else
                                Crea el primer quiz de este curso.
                            @endif
                        </p>
                        @if($searchKey || ($lesson ?? '') !== '' || ($available ?? '') !== '')
                            <a href="{{ route('manager.courses.quiz', $course->slack) }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            <button type="button" class="btn btn-primary btn-new-quiz"
                                    data-bs-toggle="modal" data-bs-target="#quiz-modal">
                                Nuevo quiz
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            @if($quizs->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $quizs->firstItem() }}–{{ $quizs->lastItem() }} de {{ $quizs->total() }} quizs
                    </span>
                    {{ $quizs->appends(request()->input())->links() }}
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
                        <label class="form-label fw-semibold">Lección</label>
                        <select id="modalLesson" class="form-select">
                            <option value="">Todas</option>
                            @foreach($lessons as $item)
                                <option value="{{ $item->id }}" {{ ($lesson ?? '') == $item->id ? 'selected' : '' }}>
                                    {{ $item->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
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
                    <a href="{{ route('manager.courses.quiz', $course->slack) }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Crear / Editar quiz --}}
    <div class="modal fade" id="quiz-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="formQuiz">
                    <div class="modal-header">
                        <h5 class="modal-title" id="quizModalTitle">Nuevo quiz</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="quizSlack" name="slack" value="">
                        <input type="hidden" name="course" value="{{ $course->slack }}">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quizTitle" name="title" maxlength="200" placeholder="Ingresar título">
                                <label id="quizTitle-error" class="error d-none" for="quizTitle"></label>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Clase <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="quizLesson" name="lesson" data-placeholder="Selecciona una clase">
                                    @foreach($lessonOptions as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="quizLesson-error" class="error d-none" for="quizLesson"></label>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Modalidad <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="quizType" name="type" data-placeholder="Selecciona la modalidad">
                                    @foreach($types as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="quizType-error" class="error d-none" for="quizType"></label>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">¿Permite repetir el quiz? <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="quizDuration" name="duration">
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                                <label id="quizDuration-error" class="error d-none" for="quizDuration"></label>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vigencia (días) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quizDay" name="day" placeholder="Ej: 365">
                                <small class="form-text text-muted">Días que el quiz permanece disponible para el alumno</small>
                                <label id="quizDay-error" class="error d-none" for="quizDay"></label>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tiempo límite (minutos) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quizTimer" name="timer" placeholder="Ej: 30">
                                <label id="quizTimer-error" class="error d-none" for="quizTimer"></label>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cantidad preguntas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quizQuestion" name="question" placeholder="Ingresar cantidad preguntas">
                                <label id="quizQuestion-error" class="error d-none" for="quizQuestion"></label>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Preguntas correctas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="quizMark" name="mark" placeholder="Ingresar cantidad de preguntas correctas">
                                <label id="quizMark-error" class="error d-none" for="quizMark"></label>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="quizAvailable" name="available">
                                    @foreach($availables as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="quizAvailable-error" class="error d-none" for="quizAvailable"></label>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Detalle</label>
                                <div class="quill-wrapper">
                                    <div id="quizDescription"></div>
                                </div>
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

    @include('managers.includes.delete')

@endsection

@push('css')
<style>.bulk-toolbar-float { z-index: 1050; }</style>
@endpush

@push('scripts')
<script>
$(function () {

    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterLesson').val($('#modalLesson').val());
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

    // ── Crear / Editar quiz (modal) ────────────────────────────────────────────
    var quizQuill = null;
    var quizMode = 'create';

    function initQuizQuill() {
        if (quizQuill) return;
        quizQuill = new Quill('#quizDescription', {
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
    // documentReady SIN dropdownParent (los selects viven ocultos dentro del
    // modal en ese momento) — su dropdown terminaría flotando sobre <body> en
    // vez del modal. Por eso se destruye cada instancia y se re-crea con las
    // opciones correctas cada vez que el modal se abre.
    function initQuizSelect2() {
        ['#quizLesson', '#quizType', '#quizDuration', '#quizAvailable'].forEach(function (selector) {
            var $select = $(selector);
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
            $select.select2({ dropdownParent: $('#quiz-modal'), width: '100%', placeholder: $select.data('placeholder') });
        });
    }

    var quizValidator = $('#formQuiz').validate({
        ignore: '.ignore',
        rules: {
            title: { required: true, minlength: 3, maxlength: 200 },
            lesson: { required: true },
            type: { required: true },
            duration: { required: true },
            day: { required: true, number: true, min: 0, max: 999 },
            timer: { required: true, number: true, min: 0, max: 999 },
            question: { required: true, number: true, min: 0, max: 999 },
            mark: { required: true, number: true, min: 0, max: 999 },
            available: { required: true },
        },
        messages: {
            title: { required: 'El título es obligatorio.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 200 caracteres.' },
            lesson: { required: 'Selecciona una clase.' },
            type: { required: 'Selecciona una modalidad.' },
            duration: { required: 'Selecciona una opción.' },
            day: { required: 'La vigencia es obligatoria.', number: 'Solo se permiten números.', min: 'Debe ser mayor o igual a 0.', max: 'Debe ser menor a 999.' },
            timer: { required: 'El tiempo límite es obligatorio.', number: 'Solo se permiten números.', min: 'Debe ser mayor o igual a 0.', max: 'Debe ser menor a 999.' },
            question: { required: 'La cantidad de preguntas es obligatoria.', number: 'Solo se permiten números.', min: 'Debe ser mayor o igual a 0.', max: 'Debe ser menor a 999.' },
            mark: { required: 'Las preguntas correctas son obligatorias.', number: 'Solo se permiten números.', min: 'Debe ser mayor o igual a 0.', max: 'Debe ser menor a 999.' },
            available: { required: 'Selecciona un estado.' },
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element).addClass('error').removeClass('d-none');
        },
        submitHandler: function (form) {
            var formData = new FormData(form);
            var description = quizQuill ? quizQuill.root.innerHTML.replace('<p><br></p>', '') : '';
            formData.append('description', description);

            var url = quizMode === 'edit'
                ? "{{ route('manager.courses.quiz.update') }}"
                : "{{ route('manager.courses.quiz.store') }}";

            var $submitButton = $('#formQuiz button[type="submit"]').prop('disabled', true);
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
                        $('#quiz-modal').modal('hide');
                        toastr.success(response.message);
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        toastr.warning(response.message || 'No se pudo guardar.');
                    }
                },
                error: function () {
                    $submitButton.prop('disabled', false).text(submitOriginalText);
                    toastr.error('Ocurrió un error al guardar el quiz.');
                },
            });
        },
    });

    function resetQuizForm() {
        $('#formQuiz')[0].reset();
        quizValidator.resetForm();
        $('#formQuiz .is-invalid').removeClass('is-invalid');
        $('#quizSlack').val('');
        $('#quizLesson').val('').trigger('change');
        $('#quizType').val('').trigger('change');
        $('#quizDuration').val('1').trigger('change');
        $('#quizAvailable').val('1').trigger('change');
        if (quizQuill) quizQuill.setText('');
    }

    function populateQuizForm(data) {
        $('#quizSlack').val(data.slack);
        $('#quizTitle').val(data.title);
        $('#quizLesson').val(data.lesson_id != null ? String(data.lesson_id) : '').trigger('change');
        $('#quizType').val(String(data.type)).trigger('change');
        $('#quizDuration').val(String(data.duration)).trigger('change');
        $('#quizDay').val(data.day);
        $('#quizTimer').val(data.timer);
        $('#quizQuestion').val(data.question);
        $('#quizMark').val(data.mark);
        $('#quizAvailable').val(String(data.available)).trigger('change');
        quizQuill.root.innerHTML = data.description || '';
    }

    // Abrir en modo "crear"
    $(document).on('click', '.btn-new-quiz', function () {
        quizMode = 'create';
        $('#quizModalTitle').text('Nuevo quiz');
    });

    $('#quiz-modal').on('shown.bs.modal', function () {
        initQuizQuill();
        initQuizSelect2();
        if (quizMode === 'create') resetQuizForm();
    });

    // Abrir en modo "editar": trae los datos vía AJAX y precarga el modal.
    $(document).on('click', '.btn-edit-quiz', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');

        $.getJSON("{{ url('panel/courses/quiz/edit') }}/" + slack, function (data) {
            quizMode = 'edit';
            $('#quizModalTitle').text('Editar quiz');
            $('#quiz-modal').modal('show');

            var applyData = function () {
                initQuizQuill();
                initQuizSelect2();
                populateQuizForm(data);
            };

            if ($('#quiz-modal').hasClass('show')) {
                applyData();
            } else {
                $('#quiz-modal').one('shown.bs.modal', applyData);
            }
        }).fail(function () {
            toastr.error('No se pudo cargar el quiz.');
        });
    });

});
</script>
@endpush
