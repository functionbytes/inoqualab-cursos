@extends('layouts.managers')

@section('title', 'Categorias de instrucciones')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/instructions/categories/index.css') }}">
@endpush

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.instructions.categories.create') }}" class="btn btn-primary btn-icon" title="Nueva categoria" aria-label="Nueva categoria">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Categorias de instrucciones',
        'description' => 'Gestiona las categorias disponibles para las instrucciones',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div id="instructionCategoriesPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.instructions.categories.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.instructions.categories._table')
        </div>
    </div>

    

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'categoria(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/instructions/categories/index.js') }}"></script>
@endpush
