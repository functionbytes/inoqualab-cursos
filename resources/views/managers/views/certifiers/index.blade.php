@extends('layouts.managers')

@section('title', 'Capacitadores')

@section('page_header')
    @php ob_start(); @endphp
@can('certifiers.create')
                        <a href="{{ route('manager.certifiers.create') }}" class="btn btn-primary btn-icon" title="Nuevo capacitador" aria-label="Nuevo capacitador">{!! \App\Html\IconHelper::render('plus') !!}</a>
                        @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Capacitadores',
        'description' => 'Gestiona los capacitadores de la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="certifiers-page"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-url="{{ route('manager.certifiers.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.certifiers._table')
        </div>
    </div>

    

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'capacitador(es)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/certifiers/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/certifiers/index.js') }}"></script>
@endpush
