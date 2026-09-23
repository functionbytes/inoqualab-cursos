@extends('layouts.managers')

@section('title', 'Anuncios')

@section('page_header')
    @php ob_start(); @endphp
<button type="button" class="btn btn-primary btn-icon" id="btnNewAnnouncement"
                                data-bs-toggle="modal" data-bs-target="#announcement-modal"
                                title="Nuevo anuncio" aria-label="Nuevo anuncio">{!! \App\Html\IconHelper::render('plus') !!}</button>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Anuncios del curso',
        'description' => 'Gestiona los anuncios visibles para los estudiantes',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    @include('managers.includes.course-subnav', ['course' => $course, 'active' => 'announcements'])

    <div class="widget-content searchable-container list" id="courses-announcements-index"
         data-flash-success="{{ session('success') }}"
         data-config='@php $__jsonInline1 = [
            "courseSlack" => $course->slack,
            "routes" => [
                "bulkAction" => route("manager.courses.announcements.bulk-action"),
                "editBase" => url("panel/courses/announcements/edit"),
                "update" => route("manager.courses.announcements.update"),
                "store" => route("manager.courses.announcements.store"),
            ],
         ]; @endphp@json($__jsonInline1)'>

                <div id="ajax-table-root">
            @include('managers.views.courses.announcements._table')
        </div>
    </div>

    

    {{-- Crear / Editar anuncio --}}
    <div class="modal fade" id="announcement-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="announcementModalTitle">Nuevo anuncio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="announcementSlack" value="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="announcementTitle" maxlength="100" placeholder="Ingresar título">
                        <label id="announcementTitle-error" class="error d-none"></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                        <select class="select2 form-control" id="announcementAvailable">
                            @foreach($availables as $optId => $optLabel)
                                <option value="{{ $optId }}">{{ $optLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Detalle</label>
                        <div class="quill-wrapper">
                            <div id="announcementDescription"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="btnSaveAnnouncement" class="btn btn-primary w-100 mb-2">Guardar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'anuncio(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/courses/announcements/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/courses/announcements/index.js') }}"></script>
@endpush
