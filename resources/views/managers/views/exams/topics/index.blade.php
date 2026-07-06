@extends('layouts.managers')

@section('title', 'Preguntas')

@section('content')


    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Preguntas del examen</h5>
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
                                    <th>Titulo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($questions as $question)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words(Str::ucfirst(Str::lower($question->question)), 10, '...') }}</div>
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
                                                           data-url="{{ route('manager.courses.exam.questions.destroy', $question->slack) }}"
                                                           data-title="Eliminar: {{ Str::words(Str::lower($question->question), 6, '...') }}">
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
                        <i class="fas fa-circle-question fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay preguntas
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') || ($available ?? '') !== '')
                                No hay preguntas que coincidan con los filtros aplicados.
                            @else
                                Crea la primera pregunta para este examen.
                            @endif
                        </p>
                        @if(($searchKey ?? '') || ($available ?? '') !== '')
                            <a href="{{ route('manager.courses.exam.questions', $topic->slack) }}" class="btn btn-outline-secondary">
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
                    <a href="{{ route('manager.courses.exam.questions', $topic->slack) }}" class="btn btn-secondary w-100">
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
                <div class="modal-header">
                    <h5 class="modal-title" id="questionModalTitle">Nueva pregunta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formQuestion" onsubmit="return false">
                        <input type="hidden" id="questionSlack" value="">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pregunta <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="questionText" name="question" maxlength="400" placeholder="Ingresar la pregunta">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select class="form-select" id="questionAvailable">
                                    @foreach($availables as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Respuesta(s) correcta(s) <span class="text-danger">*</span></label>
                                @if($topic->type == 1)
                                    <select class="form-select" id="questionAnswer" name="answer" multiple
                                            data-placeholder="Selecciona una o más opciones">
                                        @foreach($answers as $optId => $optLabel)
                                            <option value="{{ $optId }}">{{ $optLabel }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <select class="form-select" id="questionAnswer" name="answer"
                                            data-placeholder="Selecciona la opción correcta">
                                        @foreach($answers as $optId => $optLabel)
                                            <option value="{{ $optId }}">{{ $optLabel }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>

                        @if($topic->type == 1)
                            <div class="row g-3 mt-0">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">A <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="questionA" name="a" maxlength="200" placeholder="Ingresar la respuesta a">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">B <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="questionB" name="b" maxlength="200" placeholder="Ingresar la respuesta b">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">C <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="questionC" name="c" maxlength="200" placeholder="Ingresar la respuesta c">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">D <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="questionD" name="d" maxlength="200" placeholder="Ingresar la respuesta d">
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer flex-column">
                    <button type="submit" form="formQuestion" id="btnSaveQuestion" class="btn btn-primary w-100 mb-2">Guardar</button>
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

    // ── Crear / Editar pregunta (modal) ────────────────────────────────────────
    // El tipo de pregunta lo define el topic (examen) y es fijo: 1 = selección
    // múltiple (A/B/C/D), 0 = falso/verdadero. El formulario ya se renderizó
    // con la variante correcta desde el servidor.
    var questionMode = 'create';
    var questionType = {{ (int) $topic->type }};

    // select2 se inicializa perezosamente en "shown.bs.modal": el modal está
    // oculto al cargar la página y select2 calcula mal el ancho sobre
    // elementos ocultos. dropdownParent evita que el desplegable quede
    // clipeado o mal posicionado dentro del modal.
    $('#question-modal').on('shown.bs.modal', function () {
        if (typeof $.fn.select2 === 'undefined') return;

        if (!$('#questionAvailable').hasClass('select2-hidden-accessible')) {
            $('#questionAvailable').select2({ width: '100%', dropdownParent: $('#question-modal') });
        }
        if (!$('#questionAnswer').hasClass('select2-hidden-accessible')) {
            $('#questionAnswer').select2({
                width: '100%',
                dropdownParent: $('#question-modal'),
                placeholder: $('#questionAnswer').data('placeholder'),
            });
        }
    });

    function resetQuestionForm() {
        $('#questionSlack').val('');
        $('#questionText, #questionA, #questionB, #questionC, #questionD').val('');
        $('#questionAvailable').val('1');
        $('#questionAnswer').val(null).trigger('change');
        $('#formQuestion').validate().resetForm();
        $('#formQuestion .is-invalid').removeClass('is-invalid');
    }

    $('.btn-new-question').on('click', function () {
        questionMode = 'create';
        $('#questionModalTitle').text('Nueva pregunta');
        resetQuestionForm();
    });

    // Abrir en modo "editar": trae los datos vía AJAX y precarga el modal.
    $(document).on('click', '.btn-edit-question', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');

        $.getJSON("{{ url('panel/courses/exam/questions/edit') }}/" + slack, function (data) {
            questionMode = 'edit';
            $('#questionModalTitle').text('Editar pregunta');
            $('#questionSlack').val(data.slack);
            $('#questionText').val(data.question);
            $('#questionAvailable').val(String(data.available));
            $('#questionA').val(data.a);
            $('#questionB').val(data.b);
            $('#questionC').val(data.c);
            $('#questionD').val(data.d);

            var answerValue = questionType === 1 && data.answer ? data.answer.split(',') : data.answer;
            $('#questionAnswer').val(answerValue).trigger('change');

            $('#question-modal').modal('show');
        }).fail(function () {
            toastr.error('No se pudo cargar la pregunta.');
        });
    });

    // Reglas de validación: A/B/C/D solo aplican si el topic es de selección múltiple.
    var questionRules = {
        question: { required: true, minlength: 3, maxlength: 400 },
        answer: { required: true },
    };
    var questionMessages = {
        question: {
            required: 'La pregunta es obligatoria.',
            minlength: 'Debe contener al menos 3 caracteres.',
            maxlength: 'Debe contener como máximo 400 caracteres.',
        },
        answer: { required: 'Selecciona la respuesta correcta.' },
    };

    if (questionType === 1) {
        ['a', 'b', 'c', 'd'].forEach(function (field) {
            questionRules[field] = { required: true, minlength: 3, maxlength: 200 };
            questionMessages[field] = {
                required: 'La respuesta ' + field.toUpperCase() + ' es obligatoria.',
                minlength: 'Debe contener al menos 3 caracteres.',
                maxlength: 'Debe contener como máximo 200 caracteres.',
            };
        });
    }

    $('#formQuestion').validate({
        rules: questionRules,
        messages: questionMessages,
        errorElement: 'label',
        errorPlacement: function (error, element) {
            error.removeClass('d-none');
            var target = element.hasClass('select2-hidden-accessible') ? element.next('.select2-container') : element;
            error.insertAfter(target);
        },
        highlight: function (element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function () {
            var answer = $('#questionAnswer').val();
            if (Array.isArray(answer)) {
                answer = answer.join(',');
            }

            var formData = new FormData();
            formData.append('slack', $('#questionSlack').val());
            formData.append('question', $('#questionText').val());
            formData.append('available', $('#questionAvailable').val());
            formData.append('answer', answer);
            formData.append('topic', "{{ $topic->slack }}");
            if (questionType === 1) {
                formData.append('a', $('#questionA').val());
                formData.append('b', $('#questionB').val());
                formData.append('c', $('#questionC').val());
                formData.append('d', $('#questionD').val());
            }

            var isEdit = questionMode === 'edit';
            var url = isEdit
                ? "{{ route('manager.courses.exam.questions.update') }}"
                : "{{ route('manager.courses.exam.questions.store') }}";

            var $btn = $('#btnSaveQuestion').prop('disabled', true);
            var btnOriginalText = $btn.text();
            $btn.text('Guardando...');

            $.ajax({
                url: url,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                contentType: false,
                processData: false,
                data: formData,
                success: function (res) {
                    $btn.prop('disabled', false).text(btnOriginalText);
                    if (res.success) {
                        $('#question-modal').modal('hide');
                        toastr.success(res.message);
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        toastr.warning(res.message || 'No se pudo guardar.');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).text(btnOriginalText);
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function (field, messages) {
                            toastr.error(messages[0]);
                        });
                    } else {
                        toastr.error('Ocurrió un error al guardar la pregunta.');
                    }
                },
            });
        },
    });

});
</script>
@endpush
