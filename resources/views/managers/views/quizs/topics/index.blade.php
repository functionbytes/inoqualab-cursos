@extends('layouts.managers')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Preguntas del quiz</h5>
                        <p class="mb-0 text-muted">Gestiona las preguntas asociadas a este tema</p>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary btn-new-question"
                                data-bs-toggle="modal" data-bs-target="#question-modal">
                            Nueva pregunta
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
                                       placeholder="Buscar pregunta..."
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
                @if($questions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Pregunta</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($questions as $question)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words($question->question, 10, '...') }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($question->available == 1)
                                                <span class="badge bg-success-subtle text-success">Publico</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($question->updated_at)) }}</span>
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
                                                        <a class="dropdown-item btn-edit-question" href="#"
                                                           data-slack="{{ $question->slack }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.courses.quiz.questions.destroy', $question->slack) }}"
                                                           data-title="Eliminar: {{ Str::words($question->question, 5, '...') }}">
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
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay preguntas
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No hay preguntas que coincidan con los filtros aplicados.
                            @else
                                Crea la primera pregunta para este quiz.
                            @endif
                        </p>
                        @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @else
                            <button type="button" class="btn btn-primary btn-new-question"
                                    data-bs-toggle="modal" data-bs-target="#question-modal">
                                Nueva pregunta
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            @if($questions->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $questions->firstItem() }}–{{ $questions->lastItem() }} de {{ $questions->total() }} preguntas
                    </span>
                    {{ $questions->appends(request()->input())->links() }}
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

    {{-- Crear / Editar pregunta --}}
    <div class="modal fade" id="question-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="formQuestion">
                    <div class="modal-header">
                        <h5 class="modal-title" id="questionModalTitle">Nueva pregunta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="questionSlack" name="slack" value="">
                        <input type="hidden" name="topic" value="{{ $topic->slack }}">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Pregunta <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="question" name="question" maxlength="400" placeholder="Ingresar la pregunta">
                                <label id="question-error" class="error d-none" for="question"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select class="form-select" id="available" name="available">
                                    @foreach($availables as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Respuesta(s) correcta(s) <span class="text-danger">*</span></label>
                                @if($topic->type == 1)
                                    <select class="form-select" id="answer" name="answer" multiple
                                            data-placeholder="Selecciona una o más opciones">
                                        @foreach($answers as $optId => $optLabel)
                                            <option value="{{ $optId }}">{{ $optLabel }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <select class="form-select" id="answer" name="answer"
                                            data-placeholder="Selecciona la opción correcta">
                                        @foreach($answers as $optId => $optLabel)
                                            <option value="{{ $optId }}">{{ $optLabel }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <label id="answer-error" class="error d-none" for="answer"></label>
                            </div>

                            @if($topic->type == 1)
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">A <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="a" name="a" maxlength="200" placeholder="Ingresar la respuesta A">
                                    <label id="a-error" class="error d-none" for="a"></label>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">B <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="b" name="b" maxlength="200" placeholder="Ingresar la respuesta B">
                                    <label id="b-error" class="error d-none" for="b"></label>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">C <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="c" name="c" maxlength="200" placeholder="Ingresar la respuesta C">
                                    <label id="c-error" class="error d-none" for="c"></label>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">D <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="d" name="d" maxlength="200" placeholder="Ingresar la respuesta D">
                                    <label id="d-error" class="error d-none" for="d"></label>
                                </div>
                            @endif
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

@push('scripts')
<script>
$(function () {

    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    @if(session('error'))
        toastr.error('{{ session('error') }}');
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

    // ── Crear / Editar pregunta (modal) ───────────────────────────────────────
    var questionMode = 'create';
    var answerIsMultiple = {{ $topic->type == 1 ? 'true' : 'false' }};

    // El script global (select2.init.js) ya auto-inicializa ".select2"/selects
    // sin dropdownParent en documentReady (el modal está oculto en ese momento,
    // su dropdown terminaría flotando sobre <body>). Por eso se destruye esa
    // instancia y se re-crea con las opciones correctas cada vez que el modal se abre.
    function initQuestionSelect2() {
        [$('#available'), $('#answer')].forEach(function ($select) {
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
            $select.select2({
                width: '100%',
                dropdownParent: $('#question-modal'),
                placeholder: $select.data('placeholder'),
            });
        });
    }

    var questionValidator = $('#formQuestion').validate({
        ignore: '.ignore',
        rules: {
            question: { required: true, minlength: 3, maxlength: 400 },
            available: { required: true },
            answer: { required: true },
            a: { required: answerIsMultiple, minlength: 3, maxlength: 200 },
            b: { required: answerIsMultiple, minlength: 3, maxlength: 200 },
            c: { required: answerIsMultiple, minlength: 3, maxlength: 200 },
            d: { required: answerIsMultiple, minlength: 3, maxlength: 200 },
        },
        messages: {
            question: { required: 'La pregunta es obligatoria.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 400 caracteres.' },
            available: { required: 'Selecciona un estado.' },
            answer: { required: 'Selecciona la respuesta correcta.' },
            a: { required: 'La respuesta A es obligatoria.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 200 caracteres.' },
            b: { required: 'La respuesta B es obligatoria.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 200 caracteres.' },
            c: { required: 'La respuesta C es obligatoria.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 200 caracteres.' },
            d: { required: 'La respuesta D es obligatoria.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 200 caracteres.' },
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element).addClass('error').removeClass('d-none');
        },
        submitHandler: function (form) {
            var formData = new FormData(form);

            // El <select> de "answer" no se serializa automáticamente: si es múltiple,
            // el navegador enviaría varias entradas con la misma key y el backend solo
            // recibiría la última. Se recalcula y se une con comas explícitamente.
            var answerValue = $('#answer').val();
            formData.delete('answer');
            formData.append('answer', Array.isArray(answerValue) ? answerValue.join(',') : (answerValue || ''));

            var url = questionMode === 'edit'
                ? "{{ route('manager.courses.quiz.questions.update') }}"
                : "{{ route('manager.courses.quiz.questions.store') }}";

            var $submitButton = $('#formQuestion button[type="submit"]').prop('disabled', true);
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
                        $('#question-modal').modal('hide');
                        toastr.success(response.message);
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        toastr.warning(response.message || 'No se pudo guardar.');
                    }
                },
                error: function () {
                    $submitButton.prop('disabled', false).text(submitOriginalText);
                    toastr.error('Ocurrió un error al guardar la pregunta.');
                },
            });
        },
    });

    function resetQuestionForm() {
        $('#formQuestion')[0].reset();
        questionValidator.resetForm();
        $('#formQuestion .is-invalid').removeClass('is-invalid');
        $('#questionSlack').val('');
        $('#available').val('1').trigger('change');
        $('#answer').val(answerIsMultiple ? [] : '').trigger('change');
    }

    function populateQuestionForm(data) {
        $('#questionSlack').val(data.slack);
        $('#question').val(data.question);
        $('#available').val(String(data.available)).trigger('change');
        $('#answer').val(answerIsMultiple ? (data.answer ? data.answer.split(',') : []) : data.answer).trigger('change');
        $('#a').val(data.a || '');
        $('#b').val(data.b || '');
        $('#c').val(data.c || '');
        $('#d').val(data.d || '');
    }

    // Abrir en modo "crear"
    $(document).on('click', '.btn-new-question', function () {
        questionMode = 'create';
        $('#questionModalTitle').text('Nueva pregunta');
    });

    $('#question-modal').on('shown.bs.modal', function () {
        initQuestionSelect2();
        if (questionMode === 'create') resetQuestionForm();
    });

    // Abrir en modo "editar": trae los datos vía AJAX y precarga el modal.
    $(document).on('click', '.btn-edit-question', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');

        $.getJSON("{{ url('panel/courses/quiz/questions/edit') }}/" + slack, function (data) {
            questionMode = 'edit';
            $('#questionModalTitle').text('Editar pregunta');
            $('#question-modal').modal('show');

            var applyData = function () {
                initQuestionSelect2();
                populateQuestionForm(data);
            };

            if ($('#question-modal').hasClass('show')) {
                applyData();
            } else {
                $('#question-modal').one('shown.bs.modal', applyData);
            }
        }).fail(function () {
            toastr.error('No se pudo cargar la pregunta.');
        });
    });

});
</script>
@endpush
