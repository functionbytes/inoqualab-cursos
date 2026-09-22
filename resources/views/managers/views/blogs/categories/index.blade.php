@extends('layouts.managers')

@section('title', 'Categorias')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.blogs.categories.create') }}" class="btn btn-primary btn-icon" title="Nueva categoria" aria-label="Nueva categoria">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Categorias del blog',
        'description' => 'Gestiona las categorias para organizar las entradas del blog',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="blogs-categories-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-config='@json([
            "routes" => [
                "bulkAction" => route("manager.blogs.categories.bulk-action"),
            ],
         ])'>

                <div id="ajax-table-root">
            @include('managers.views.blogs.categories._table')
        </div>
    </div>

    

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'categoria(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/blogs/categories/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/blogs/categories/index.js') }}"></script>
@endpush
