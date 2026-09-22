@extends('layouts.managers')

@section('title', 'Etiquetas')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.blogs.tags.create') }}" class="btn btn-primary btn-icon" title="Nueva etiqueta" aria-label="Nueva etiqueta">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Etiquetas del blog',
        'description' => 'Gestiona las etiquetas para clasificar las entradas del blog',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="blogs-tags-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-config='@json([
            "routes" => [
                "bulkAction" => route("manager.blogs.tags.bulk-action"),
            ],
         ])'>

                <div id="ajax-table-root">
            @include('managers.views.blogs.tags._table')
        </div>
    </div>

    

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'etiqueta(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/blogs/tags/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/blogs/tags/index.js') }}"></script>
@endpush
