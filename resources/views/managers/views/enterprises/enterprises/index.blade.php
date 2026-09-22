@extends('layouts.managers')

@section('title', 'Empresas')

@section('page_header')
    @php ob_start(); @endphp
@can('enterprises.create')
                        <a href="{{ route('manager.enterprises.create') }}" class="btn btn-primary btn-icon" title="Nueva empresa" aria-label="Nueva empresa">{!! \App\Html\IconHelper::render('plus') !!}</a>
                        @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Empresas',
        'description' => 'Gestiona las empresas registradas en la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="enterprises-page"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-url="{{ route('manager.enterprises.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.enterprises.enterprises._table')
        </div>
    </div>

    

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'empresa(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/enterprises/enterprises/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/enterprises/enterprises/index.js') }}"></script>
@endpush
