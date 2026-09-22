@extends('layouts.managers')

@section('page_header')
    @php
        $headerActions = auth()->user()->can('courses.create')
            ? '<a href="'.e(route('manager.courses.create')).'" class="btn btn-primary btn-icon" title="Nuevo curso" aria-label="Nuevo curso">'.\App\Html\IconHelper::render('plus').'</a>'
            : null;
    @endphp
    @include('managers.includes.card', [
        'title' => 'Cursos',
        'description' => 'Gestiona el catálogo de cursos de la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="courses-index"
         data-flash-success="{{ session('success') }}"
         data-config='@json([
            "routes" => [
                "bulkAction" => route("manager.courses.bulk-action"),
            ],
         ])'>

                <div id="ajax-table-root">
            @include('managers.views.courses.courses._table')
        </div>
    </div>

    

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'curso(s)',
        'bulkActions' => [
            ['value' => 'publish', 'label' => 'Publicar'],
            ['value' => 'hide', 'label' => 'Ocultar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/courses/courses/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/courses/courses/index.js') }}"></script>
@endpush
