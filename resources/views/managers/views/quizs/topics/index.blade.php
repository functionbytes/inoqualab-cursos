@extends('layouts.managers')

@section('content')


    <div class="widget-content searchable-container list" id="quizs-topics-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-config='@php $__jsonInline1 = [
            "answerIsMultiple" => $topic->type == 1,
            "routes" => [
                "bulkAction" => route("manager.courses.quiz.questions.bulk-action"),
                "update" => route("manager.courses.quiz.questions.update"),
                "store" => route("manager.courses.quiz.questions.store"),
                "editBase" => url("panel/courses/quiz/questions/edit"),
            ],
         ]; @endphp@json($__jsonInline1)'>

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
                                    <th class="quizs-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
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
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $question->id }}">
                                        </td>
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

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'pregunta(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/quizs/topics/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/quizs/topics/index.js') }}"></script>
@endpush
