@extends('layouts.managers')

@section('title', 'Banners')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.sliders.create') }}" class="btn btn-primary btn-icon" title="Nuevo banner" aria-label="Nuevo banner">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Banners',
        'description' => 'Gestiona los banners y sliders del sitio',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div id="slidersPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.sliders.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.sliders._table')
        </div>
    </div>

    

    {{-- Bulk toolbar --}}
    <div id="bulk-toolbar" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none bulk-toolbar-float">
        <button type="button" class="btn btn-primary shadow-lg px-4"
                data-bs-toggle="modal" data-bs-target="#bulk-modal">
            <span data-bulk-count>0</span> seleccionado(s) &mdash; Aplicar acción
        </button>
    </div>

    {{-- Bulk modal --}}
    <div class="modal fade" id="bulk-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Acción masiva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Se aplicará la acción sobre <strong><span data-bulk-count>0</span> banner(es)</strong>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Acción</label>
                        <select id="bulk-action-select" class="form-select">
                            <option value="">Seleccionar acción...</option>
                            <option value="publish">Publicar</option>
                            <option value="hide">Ocultar</option>
                            <option value="delete">Eliminar</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button id="btn-bulk-apply" type="button" class="btn btn-primary w-100 mb-2">
                        Aplicar
                    </button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/sliders/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/settings/sliders/index.js') }}"></script>
@endpush
