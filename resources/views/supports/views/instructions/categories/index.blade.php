@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.instructions.categories.create') }}" class="btn btn-primary btn-icon" title="Nueva categoría" aria-label="Nueva categoría">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Categorías de instrucciones',
        'description' => 'Gestiona las categorías de instrucciones del portal',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list" id="instructions-categories-list"
         data-bulk-url="{{ route('support.instructions.categories.bulk-action') }}">

        <div id="ajax-table-root">
            @include('supports.views.instructions.categories._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'categoría(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/instructions/categories/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('supports/js/views/instructions/categories/index.js') }}"></script>
@endpush
