@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
    <div class="btn-group">
        <button type="button" class="btn btn-icon btn-actions-icon dropdown-toggle arrow-none"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Acciones">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('support.distributors.orders.resumen', $distributor->slack) }}">
                Resumen
            </a>
            <a class="dropdown-item" href="{{ route('support.distributors.orders.reports', $distributor->slack) }}">
                Reporte
            </a>
        </div>
    </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Ordenes de ' . $distributor->title,
        'description' => 'Gestiona y consulta las órdenes registradas para este distribuidor',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}">

        <div id="ajax-table-root">
            @include('supports.views.distributors.orders.orders._table')
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('supports/js/views/distributors/orders/orders/index.js') }}"></script>
@endpush
