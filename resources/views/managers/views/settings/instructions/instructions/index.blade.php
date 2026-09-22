@extends('layouts.managers')

@section('title', 'Instrucciones')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/instructions/instructions/index.css') }}">
@endpush

@section('page_header')
    @php ob_start(); @endphp
@can('instructions.create')
                        <a href="{{ route('manager.instructions.create') }}" class="btn btn-primary btn-icon" title="Nueva instrucción" aria-label="Nueva instrucción">{!! \App\Html\IconHelper::render('plus') !!}</a>
                        @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Instrucciones',
        'description' => 'Gestiona las instrucciones y su visibilidad',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div id="instructionsPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.instructions.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.instructions.instructions._table')
        </div>
    </div>

    

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'instrucción(es)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/instructions/instructions/index.js') }}"></script>
@endpush
