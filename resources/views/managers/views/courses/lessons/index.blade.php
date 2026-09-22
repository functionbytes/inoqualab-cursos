@extends('layouts.managers')

@section('title', 'Clases')

@section('page_header')
    @php ob_start(); @endphp
<button type="button" class="btn btn-primary btn-new-lesson"
                                data-bs-toggle="modal" data-bs-target="#lesson-modal">
                            Nueva clase
                        </button>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Clases del curso',
        'description' => 'Gestiona las clases y su contenido',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="courses-lessons-index"
         data-flash-success="{{ session('success') }}"
         data-config='@php $__jsonInline1 = [
            "nextPosition" => (int) $nextPosition,
            "routes" => [
                "bulkAction" => route("manager.courses.lessons.bulk-action"),
                "editBase" => url("panel/courses/lessons/edit"),
                "update" => route("manager.courses.lessons.update"),
                "store" => route("manager.courses.lessons.store"),
            ],
         ]; @endphp@json($__jsonInline1)'>

                <div id="ajax-table-root">
            @include('managers.views.courses.lessons._table')
        </div>
    </div>

    

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'clase(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    {{-- Crear / Editar clase --}}
    <div class="modal fade" id="lesson-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <form id="formLessons" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="lessonModalTitle">Nueva clase</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="lessonSlack" name="slack" value="">
                        <input type="hidden" name="course" value="{{ $course->slack }}">
                        <textarea class="d-none" id="detail" name="detail"></textarea>

                        <p class="text-muted mb-3">
                            El tipo de contenido (video, audio, imagen, PDF, etc.) determina qué campos deberás completar.
                        </p>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" maxlength="100" placeholder="Ingresar título">
                                <label id="title-error" class="error d-none" for="title"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tema <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="chapter" name="chapter" data-placeholder="Selecciona un tema">
                                    @foreach($chapterOptions as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="chapter-error" class="error d-none" for="chapter"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="type" name="type" data-placeholder="Selecciona un tipo">
                                    @foreach($typeOptions as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="type-error" class="error d-none" for="type"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Posición <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="position" name="position" placeholder="Ingresar posición">
                                <label id="position-error" class="error d-none" for="position"></label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="available" name="available">
                                    @foreach($availables as $optId => $optLabel)
                                        <option value="{{ $optId }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>

                            <div class="col-md-6 d-none divAudiovisual">
                                <label class="form-label fw-semibold">Plataforma <span class="text-danger">*</span></label>
                                <select class="select2 form-control" id="platform" name="platform" data-placeholder="Selecciona la plataforma">
                                    <option value=""></option>
                                    <option value="youtube">YouTube</option>
                                    <option value="vimeo">Vimeo</option>
                                </select>
                                <label id="platform-error" class="error d-none" for="platform"></label>
                            </div>

                            <div class="col-md-6 d-none divAudiovisual">
                                <label class="form-label fw-semibold">Link <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="url" name="url" placeholder="Ej: https://www.youtube.com/watch?v=... o https://vimeo.com/...">
                                <small id="url-platform" class="d-block mt-1"></small>
                                <label id="url-error" class="error d-none" for="url"></label>
                            </div>

                            <div class="col-md-6 d-none divAudiovisual divAudio">
                                <label class="form-label fw-semibold">Duración <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="duration" name="duration" placeholder="Ingresar duración">
                                <label id="duration-error" class="error d-none" for="duration"></label>
                            </div>

                            <div class="col-md-6 d-none divAudio divFiles">
                                <label class="form-label fw-semibold">Archivo</label>
                                <input type="file" class="form-control" id="file" name="file">
                                <small id="current-file-hint" class="form-text text-muted d-none"></small>
                                <label id="file-error" class="error d-none" for="file"></label>
                            </div>

                            <div class="col-md-6 d-none divFiles">
                                <label class="form-label fw-semibold">Tamaño</label>
                                <input type="text" class="form-control" id="size" name="size" readonly placeholder="Se calcula automáticamente">
                                <small class="form-text text-muted">Se completa solo al subir el archivo, en MB</small>
                                <label id="size-error" class="error d-none" for="size"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Detalle</label>
                                <div class="quill-wrapper">
                                    <div id="details"></div>
                                </div>
                                <label id="detail-error" class="error d-none" for="detail"></label>
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

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/courses/lessons/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/libs/jquery-ui/dist/jquery-ui.min.js') }}"></script>
<script src="{{ asset('managers/js/views/courses/lessons/index.js') }}"></script>
@endpush
