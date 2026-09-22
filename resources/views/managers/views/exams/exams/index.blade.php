@extends('layouts.managers')

@section('title', 'Examenes')

@section('page_header')
    @php ob_start(); @endphp
<button type="button" class="btn btn-primary btn-new-exam"
                                data-bs-toggle="modal" data-bs-target="#exam-modal">
                            Nuevo examen
                        </button>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Examenes del curso',
        'description' => 'Gestiona los examenes y sus preguntas',
        'actions' => $headerActions,
    ])
@endsection

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

                <div id="ajax-table-root">
            @include('managers.views.exams.exams._table')
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
