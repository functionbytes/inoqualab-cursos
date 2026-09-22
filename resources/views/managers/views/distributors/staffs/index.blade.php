@extends('layouts.managers')

@section('title', 'Empleados')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.distributors.staffs.create', $distributor->slack) }}" class="btn btn-primary btn-icon" title="Nuevo empleado" aria-label="Nuevo empleado">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Empleados del distribuidor',
        'description' => 'Gestiona el personal asignado a este distribuidor',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="distributor-staffs-page"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-url="{{ route('manager.distributors.staffs.bulk-action', $distributor->slack) }}">

                <div id="ajax-table-root">
            @include('managers.views.distributors.staffs._table')
        </div>
    </div>

    

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'empleado(s)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/distributors/staffs/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/distributors/staffs/index.js') }}"></script>
@endpush
