@extends('layouts.managers')

@section('title', 'Listas de campaña')

@section('page_header')
    @php ob_start(); @endphp
    <a href="{{ route('manager.newsletter.lists.create') }}" class="btn btn-primary btn-icon" title="Nueva lista" aria-label="Nueva lista">
        {!! \App\Html\IconHelper::render('plus') !!}
    </a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Listas de campaña',
        'description' => 'Segmentos de suscriptores. Las listas dinámicas se pueblan solas por eventos.',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list" id="newsletter-lists-page"
         data-flash-success="{{ session('success') }}"
         data-bulk-url="{{ route('manager.newsletter.lists.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.newsletter.lists._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'lista(s)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/newsletter/lists/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/newsletter/lists/index.js') }}"></script>
@endpush
