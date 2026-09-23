@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.faqs.create') }}" class="btn btn-primary btn-icon" title="Nueva pregunta" aria-label="Nueva pregunta">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Preguntas frecuentes',
        'description' => 'Gestiona las preguntas frecuentes del portal',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div id="faqsPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-bulk-action-url="{{ route('support.faqs.bulk-action') }}">

        <div id="ajax-table-root">
            @include('supports.views.faqs.faqs._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'pregunta(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/faqs/faqs/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('supports/js/views/faqs/faqs/index.js') }}"></script>
@endpush
