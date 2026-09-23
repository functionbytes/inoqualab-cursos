@extends('layouts.managers')

@section('title', 'Ordenes')

@section('page_header')
    @php ob_start(); @endphp
    <a href="{{ route('manager.orders.resumen') }}" class="btn btn-outline-secondary">
        Resumen
    </a>
    <a href="{{ route('manager.orders.report') }}" class="btn btn-primary">
        Reporte
    </a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Ordenes',
        'description' => 'Gestiona y consulta las órdenes registradas',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="orders-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.orders.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.orders.orders._table')
        </div>
    </div>

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'orden(es)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/shared/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/orders/orders/index.js') }}"></script>
@endpush
