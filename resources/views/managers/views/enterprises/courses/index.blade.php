@extends('layouts.managers')

@section('title', 'Cursos de empresa')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.enterprises.courses.create', $enterprise->slack) }}" class="btn btn-primary btn-icon" title="Agregar curso" aria-label="Agregar curso">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Cursos asignados',
        'description' => 'Gestiona los cursos vinculados a esta empresa',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="enterprise-courses-page"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-url="{{ route('manager.enterprises.courses.bulk-action', $enterprise->slack) }}">

                <div id="ajax-table-root">
            @include('managers.views.enterprises.courses._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'curso(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Quitar de la empresa'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/enterprises/courses/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/enterprises/courses/index.js') }}"></script>
@endpush
