@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<button type="button" class="btn btn-primary btn-new-question"
                                data-bs-toggle="modal" data-bs-target="#question-modal">
                            Nueva pregunta
                        </button>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Preguntas del quiz',
        'description' => 'Gestiona las preguntas asociadas a este tema',
        'actions' => $headerActions,
    ])
@endsection

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

                <div id="ajax-table-root">
            @include('managers.views.quizs.topics._table')
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
