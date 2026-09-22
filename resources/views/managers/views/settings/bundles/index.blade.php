@extends('layouts.managers')

@section('title', 'Paquetes')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/bundles/index.css') }}">
@endpush

@section('page_header')
    @php ob_start(); @endphp
@can('bundles.create')
                        <a href="{{ route('manager.bundles.create') }}" class="btn btn-primary btn-icon" title="Nuevo paquete" aria-label="Nuevo paquete">{!! \App\Html\IconHelper::render('plus') !!}</a>
                        @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Paquetes de cursos',
        'description' => 'Gestiona los paquetes disponibles en la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div id="bundlesPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.bundles.bulk-action') }}"
         data-toggle-url="{{ route('manager.bundles.toggle') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.bundles._table')
        </div>
    </div>

    

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'paquete(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/bundles/index.js') }}"></script>
@endpush
