@extends('layouts.managers')

@section('title', 'Examenes')

@section('content')


    <div class="widget-content searchable-container list" id="exams-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-config='@php $__jsonInline1 = [
            "routes" => [
                "bulkAction" => route("manager.courses.exam.bulk-action"),
                "update" => route("manager.courses.exam.update"),
                "store" => route("manager.courses.exam.store"),
                "editBase" => url("panel/courses/exam/edit"),
            ],
         ]; @endphp@json($__jsonInline1)'>

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Examenes del curso</h5>
                        <p class="mb-0 text-muted">Gestiona los examenes y sus preguntas</p>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary btn-new-exam"
                                data-bs-toggle="modal" data-bs-target="#exam-modal">
                            Nuevo examen
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
                @if($exams->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="exams-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Titulo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($exams as $exam)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $exam->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::upper(Str::lower($exam->title)) }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($exam->available)
                                                <span class="badge bg-success-subtle text-success">Publico</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($exam->updated_at)) }}</span>
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
                                                           href="{{ route('manager.courses.exam.questions', $exam->slack) }}">
                                                            Preguntas
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item btn-edit-exam" href="#"
                                                           data-slack="{{ $exam->slack }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.courses.exam.destroy', $exam->slack) }}"
                                                           data-title="Eliminar: {{ $exam->title }}">
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
                        <i class="fas fa-file-alt fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay examenes
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') || ($available ?? '') !== '')
                                No hay examenes que coincidan con los filtros aplicados.
                            @else
                                Crea el primer examen para este curso.
                            @endif
                        </p>
                        @if(($searchKey ?? '') || ($available ?? '') !== '')
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            <button type="button" class="btn btn-primary btn-new-exam"
                                    data-bs-toggle="modal" data-bs-target="#exam-modal">
                                Nuevo examen
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            @if($exams->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $exams->firstItem() }}–{{ $exams->lastItem() }} de {{ $exams->total() }} examenes
                    </span>
                    {{ $exams->appends(request()->input())->links() }}
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

    {{-- Crear / Editar examen --}}
    <div class="modal fade" id="exam-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="formExam">
                    <div class="modal-header">
                        <h5 class="modal-title" id="examModalTitle">Nuevo examen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="examSlack" name="slack" value="">
                        <input type="hidden" name="course" value="{{ $course->slack }}">

                        <p class="text-muted mb-3">
                            Configura el examen final de este curso. Después de guardarlo podrás agregar sus preguntas.
                        </p>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="examTitle" name="title" maxlength="200" placeholder="Ingresar título">
                                <label id="title-error" class="error d-none" for="examTitle"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Modalidad <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="examType" name="type" data-placeholder="Selecciona la modalidad">
                                    @foreach($types as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="type-error" class="error d-none" for="examType"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">¿Permite repetir el examen? <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="examDuration" name="duration">
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                                <label id="duration-error" class="error d-none" for="examDuration"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vigencia (días) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="examDay" name="day" placeholder="Ej: 365">
                                <small class="form-text text-muted">Días que el examen permanece disponible para el alumno</small>
                                <label id="day-error" class="error d-none" for="examDay"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tiempo límite (minutos) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="examTimer" name="timer" placeholder="Ej: 60">
                                <label id="timer-error" class="error d-none" for="examTimer"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cantidad preguntas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="examQuestion" name="question" placeholder="Ingresar cantidad preguntas">
                                <label id="question-error" class="error d-none" for="examQuestion"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Preguntas correctas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="examMark" name="mark" placeholder="Ingresar cantidad de preguntas correctas">
                                <label id="mark-error" class="error d-none" for="examMark"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="examAvailable" name="available">
                                    @foreach($availables as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="available-error" class="error d-none" for="examAvailable"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Detalle</label>
                                <div class="quill-wrapper">
                                    <div id="examDescription"></div>
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
        'bulkEntityLabel' => 'examen(es)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/exams/exams/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/exams/exams/index.js') }}"></script>
@endpush
