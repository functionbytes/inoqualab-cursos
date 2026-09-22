@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<button type="button" class="btn btn-primary btn-new-quiz"
                                data-bs-toggle="modal" data-bs-target="#quiz-modal">
                            Nuevo quiz
                        </button>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Quizs del curso',
        'description' => 'Gestiona los quizs y sus preguntas',
        'actions' => $headerActions,
    ])
@endsection

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

                <div id="ajax-table-root">
            @include('managers.views.quizs.quizs._table')
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
