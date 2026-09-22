@extends('layouts.managers')

@section('title', 'Noticias')

@section('page_header')
    @php ob_start(); @endphp
@can('blogs.create')
                        <a href="{{ route('manager.blogs.create') }}" class="btn btn-primary btn-icon" title="Nueva noticia" aria-label="Nueva noticia">{!! \App\Html\IconHelper::render('plus') !!}</a>
                        @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Noticias',
        'description' => 'Gestiona las noticias y artículos del blog',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="blogs-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-config='@json([
            "routes" => [
                "bulkAction" => route("manager.blogs.bulk-action"),
            ],
         ])'>

                <div id="ajax-table-root">
            @include('managers.views.blogs.blogs._table')
        </div>
    </div>

    

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'noticia(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/blogs/blogs/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/blogs/blogs/index.js') }}"></script>
@endpush
