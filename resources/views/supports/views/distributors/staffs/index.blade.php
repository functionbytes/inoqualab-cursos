@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
    <div class="btn-group">
        <button type="button" class="btn btn-icon btn-actions-icon dropdown-toggle arrow-none"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Acciones">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('support.distributors.staffs.reports', $distributor->slack) }}">
                Reporte
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('support.distributors.staffs.create', $distributor->slack) }}">
                Nuevo empleado
            </a>
        </div>
    </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Empleados de ' . $distributor->title,
        'description' => 'Gestiona el personal asignado a este distribuidor',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-bulk-url="{{ route('support.distributors.staffs.bulk-action') }}"
         data-bulk-entity-label="empleado(s)">

        <div id="ajax-table-root">
            @include('supports.views.distributors.staffs._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'empleado(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('supports/css/views/distributors/staffs/index.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('supports/js/views/distributors/staffs/index.js') }}"></script>
@endpush
