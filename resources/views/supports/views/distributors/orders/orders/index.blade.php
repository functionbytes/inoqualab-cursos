@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.distributors.orders.resumen', $distributor->slack) }}" class="btn btn-outline-secondary">
                            Resumen
                        </a>
                        <a href="{{ route('support.distributors.orders.reports', $distributor->slack) }}" class="btn btn-primary">
                            Reporte
                        </a>
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
