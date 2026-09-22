@extends('layouts.managers')

@section('title', 'Categorias')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.categories.courses.create') }}" class="btn btn-primary btn-icon" title="Nueva categoria" aria-label="Nueva categoria">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Categorias de cursos',
        'description' => 'Gestiona las categorias del catálogo',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="courses-categories-index"
         data-flash-success="{{ session('success') }}"
         data-config='@json([
            "routes" => [
                "bulkAction" => route("manager.categories.courses.bulk-action"),
            ],
         ])'>

                <div id="ajax-table-root">
            @include('managers.views.courses.categories._table')
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
<link rel="stylesheet" href="{{ asset('managers/css/views/courses/categories/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/courses/categories/index.js') }}"></script>
@endpush
