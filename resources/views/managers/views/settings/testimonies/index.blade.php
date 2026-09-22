@extends('layouts.managers')

@section('title', 'Testimonios')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/testimonies/index.css') }}">
@endpush

@section('page_header')
    @php ob_start(); @endphp
@can('testimonies.create')
                        <a href="{{ route('manager.testimonies.create') }}" class="btn btn-primary btn-icon" title="Nuevo testimonio" aria-label="Nuevo testimonio">{!! \App\Html\IconHelper::render('plus') !!}</a>
                        @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Testimonios',
        'description' => 'Gestiona los testimonios visibles en la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div id="testimoniesPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.testimonies.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.testimonies._table')
        </div>
    </div>

    

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'testimonio(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/testimonies/index.js') }}"></script>
@endpush
