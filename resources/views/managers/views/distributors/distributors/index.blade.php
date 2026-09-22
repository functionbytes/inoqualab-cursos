@extends('layouts.managers')

@section('title', 'Distribuidores')

@section('page_header')
    @php ob_start(); @endphp
@can('distributors.create')
                        <a href="{{ route('manager.distributors.create') }}" class="btn btn-primary btn-icon" title="Nuevo distribuidor" aria-label="Nuevo distribuidor">{!! \App\Html\IconHelper::render('plus') !!}</a>
                        @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Distribuidores',
        'description' => 'Gestiona los distribuidores de la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="distributors-page"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-url="{{ route('manager.distributors.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.distributors.distributors._table')
        </div>
    </div>

    

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'distribuidor(es)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/distributors/distributors/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/distributors/distributors/index.js') }}"></script>
@endpush
