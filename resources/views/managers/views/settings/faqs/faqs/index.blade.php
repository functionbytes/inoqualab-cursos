@extends('layouts.managers')

@section('title', 'Preguntas frecuentes')

@section('page_header')
    @php ob_start(); @endphp
@can('faqs.create')
                        <a href="{{ route('manager.faqs.create') }}" class="btn btn-primary btn-icon" title="Nueva pregunta" aria-label="Nueva pregunta">{!! \App\Html\IconHelper::render('plus') !!}</a>
                        @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Preguntas frecuentes',
        'description' => 'Gestiona las preguntas y respuestas del sitio',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div id="faqsPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.faqs.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.faqs.faqs._table')
        </div>
    </div>

    

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'pregunta(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/faqs/faqs/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/settings/faqs/faqs/index.js') }}"></script>
@endpush
