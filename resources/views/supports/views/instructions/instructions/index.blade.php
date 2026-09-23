@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.instructions.create') }}" class="btn btn-primary btn-icon" title="Nueva instrucción" aria-label="Nueva instrucción">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Instrucciones',
        'description' => 'Gestiona las instrucciones de ayuda del portal',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list" id="instructions-list"
         data-bulk-url="{{ route('support.instructions.bulk-action') }}">

        <div id="ajax-table-root">
            @include('supports.views.instructions.instructions._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'instrucción(es)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/instructions/instructions/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('supports/js/views/instructions/instructions/index.js') }}"></script>
@endpush
