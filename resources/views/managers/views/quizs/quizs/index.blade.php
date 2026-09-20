@extends('layouts.managers')

@section('content')


    <div class="widget-content searchable-container list" id="quizs-index"
         data-flash-success="{{ session('success') }}"
         data-config='@php $__jsonInline1 = [
            "routes" => [
                "bulkAction" => route("manager.courses.quiz.bulk-action"),
                "update" => route("manager.courses.quiz.update"),
                "store" => route("manager.courses.quiz.store"),
                "editBase" => url("panel/courses/quiz/edit"),
            ],
         ]; @endphp@json($__jsonInline1)'>

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
                                    <th class="quizs-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
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
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $quiz->id }}">
                                        </td>
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

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'quiz(zes)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/quizs/quizs/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/quizs/quizs/index.js') }}"></script>
@endpush
