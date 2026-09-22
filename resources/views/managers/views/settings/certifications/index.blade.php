@extends('layouts.managers')

@section('title', 'Certificados')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/certifications/index.css') }}">
@endpush

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.certifications.create') }}" class="btn btn-primary btn-icon" title="Nuevo certificado" aria-label="Nuevo certificado">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Certificados',
        'description' => 'Gestiona los certificados disponibles en la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div id="certificationsPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.certifications.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.certifications._table')
        </div>
    </div>

    

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'certificado(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/certifications/index.js') }}"></script>
@endpush
