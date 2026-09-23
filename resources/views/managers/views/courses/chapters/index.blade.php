@extends('layouts.managers')

@section('title', 'Temas')

@section('page_header')
    @php ob_start(); @endphp
<button type="button" class="btn btn-primary btn-icon btn-new-chapter"
                                data-bs-toggle="modal" data-bs-target="#chapter-modal"
                                title="Nuevo tema" aria-label="Nuevo tema">{!! \App\Html\IconHelper::render('plus') !!}</button>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Temas del curso',
        'description' => 'Gestiona los temas y su orden de aparición',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    @include('managers.includes.course-subnav', ['course' => $course, 'active' => 'chapters'])

    <div class="widget-content searchable-container list" id="courses-chapters-index"
         data-flash-success="{{ session('success') }}"
         data-config='@php $__jsonInline1 = [
            "courseSlack" => $course->slack,
            "nextPosition" => (int) $nextPosition,
            "routes" => [
                "bulkAction" => route("manager.courses.chapters.bulk-action"),
                "editBase" => url("panel/courses/chapters/edit"),
                "update" => route("manager.courses.chapters.update"),
                "store" => route("manager.courses.chapters.store"),
            ],
         ]; @endphp@json($__jsonInline1)'>

                <div id="ajax-table-root">
            @include('managers.views.courses.chapters._table')
        </div>
    </div>

    

    {{-- Crear / Editar tema --}}
    <div class="modal fade" id="chapter-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chapterModalTitle">Nuevo tema</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="chapterSlack" value="">
                    <p class="text-muted mb-3">
                        Completa los datos del tema. Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                    </p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="chapterTitle" maxlength="100" placeholder="Ingresar título">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Posición</label>
                            <input type="number" class="form-control" id="chapterPosition" min="1" placeholder="Ingresar posición">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                            <select class="select2 form-control" id="chapterAvailable">
                                @foreach($availables as $optId => $optLabel)
                                    <option value="{{ $optId }}">{{ $optLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <div class="quill-wrapper">
                                <div id="chapterDescription"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="btnSaveChapter" class="btn btn-primary w-100 mb-2">Guardar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'tema(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/courses/chapters/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/libs/jquery-ui/dist/jquery-ui.min.js') }}"></script>
<script src="{{ asset('managers/js/views/courses/chapters/index.js') }}"></script>
@endpush
