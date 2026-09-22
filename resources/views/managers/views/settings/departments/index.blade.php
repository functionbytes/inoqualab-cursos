@extends('layouts.managers')

@section('title', 'Departamentos')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/departments/index.css') }}">
@endpush

@section('page_header')
    @php ob_start(); @endphp
@can('departments.create')
                        <a href="{{ route('manager.departments.create') }}" class="btn btn-primary btn-icon" title="Nuevo departamento" aria-label="Nuevo departamento">{!! \App\Html\IconHelper::render('plus') !!}</a>
                        @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Departamentos',
        'description' => 'Gestiona los departamentos de la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div id="departmentsPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-bulk-action-url="{{ route('manager.departments.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.departments._table')
        </div>
    </div>

    

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'departamento(s)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/departments/index.js') }}"></script>
@endpush
