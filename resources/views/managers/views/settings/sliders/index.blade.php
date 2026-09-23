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

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'banner(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/sliders/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/settings/sliders/index.js') }}"></script>
@endpush
