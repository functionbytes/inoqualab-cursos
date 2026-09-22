@extends('layouts.managers')

@section('title', 'Documentos')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/documents/index.css') }}">
@endpush

@section('page_header')
    @php ob_start(); @endphp
@can('documents.create')
                        <a href="{{ route('manager.documents.create') }}" class="btn btn-primary btn-icon" title="Nuevo documento" aria-label="Nuevo documento">{!! \App\Html\IconHelper::render('plus') !!}</a>
                        @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Documentos',
        'description' => 'Gestiona los documentos y archivos de la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div id="documentsPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.documents.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.documents._table')
        </div>
    </div>

    

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'documento(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/documents/index.js') }}"></script>
@endpush
