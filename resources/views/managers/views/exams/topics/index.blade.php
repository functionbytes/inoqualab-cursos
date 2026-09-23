@extends('layouts.managers')

@section('title', 'Preguntas')

@section('page_header')
    @php ob_start(); @endphp
<button type="button" class="btn btn-primary btn-icon btn-new-question"
                                data-bs-toggle="modal" data-bs-target="#question-modal"
                                title="Nueva pregunta" aria-label="Nueva pregunta">{!! \App\Html\IconHelper::render('plus') !!}</button>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Preguntas del examen',
        'description' => 'Gestiona las preguntas asociadas a este tema',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="exams-topics-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-config='@php $__jsonInline1 = [
            "questionType" => (int) $topic->type,
            "topicSlack" => $topic->slack,
            "routes" => [
                "bulkAction" => route("manager.courses.exam.questions.bulk-action"),
                "editBase" => url("panel/courses/exam/questions/edit"),
                "update" => route("manager.courses.exam.questions.update"),
                "store" => route("manager.courses.exam.questions.store"),
            ],
         ]; @endphp@json($__jsonInline1)'>

                <div id="ajax-table-root">
            @include('managers.views.exams.topics._table')
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
                    <form id="formQuestion">
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
<link rel="stylesheet" href="{{ asset('managers/css/views/exams/topics/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/exams/topics/index.js') }}"></script>
@endpush
