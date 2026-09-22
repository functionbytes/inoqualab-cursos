@extends('layouts.managers')

@section('title', 'Aliados')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/trusteds/index.css') }}">
@endpush

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.trusteds.create') }}" class="btn btn-primary btn-icon" title="Nuevo aliado" aria-label="Nuevo aliado">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Aliados',
        'description' => 'Gestiona los aliados y patrocinadores de la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div id="trustedsPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.trusteds.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.trusteds._table')
        </div>
    </div>

    

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'aliado(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/trusteds/index.js') }}"></script>
@endpush
