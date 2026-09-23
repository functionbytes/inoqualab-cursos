@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.documents.create') }}" class="btn btn-primary btn-icon" title="Nuevo documento" aria-label="Nuevo documento">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Documentos',
        'description' => 'Gestiona los documentos y archivos del portal',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div id="documentsPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-bulk-action-url="{{ route('support.documents.bulk-action') }}">

        <div id="ajax-table-root">
            @include('supports.views.documents._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'documento(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/documents/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('supports/js/views/documents/index.js') }}"></script>
@endpush
