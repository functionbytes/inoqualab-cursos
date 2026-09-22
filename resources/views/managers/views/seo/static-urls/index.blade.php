@extends('layouts.managers')

@section('title', 'URLs estáticas del sitemap')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.seo.static-urls.create') }}" class="btn btn-primary btn-icon" title="Nueva URL" aria-label="Nueva URL">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'URLs estáticas del sitemap',
        'description' => 'Gestiona las URLs estáticas que se incluirán en el sitemap XML',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}" data-flash-error="{{ session('error') }}">

                <div id="ajax-table-root">
            @include('managers.views.seo.static-urls._table')
        </div>
    </div>

    <div id="bulk-config" class="d-none" data-bulk-url="{{ route('manager.seo.static-urls.bulk-action') }}"></div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'URL(s)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/shared/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/seo/static-urls/index.js') }}"></script>
@endpush
