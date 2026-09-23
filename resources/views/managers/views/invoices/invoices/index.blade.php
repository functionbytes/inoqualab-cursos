@extends('layouts.managers')

@section('title', 'Facturación')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.invoices.report') }}" class="btn btn-primary">
                            Reporte
                        </a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Facturas',
        'description' => 'Gestiona las facturas y pagos de la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="invoices-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.invoices.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.invoices.invoices._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'factura(s)',
        'bulkActions' => [
            ['value' => 'generada', 'label' => 'Marcar como Generada'],
            ['value' => 'pendiente', 'label' => 'Marcar como Pendiente'],
            ['value' => 'pagada', 'label' => 'Marcar como Pagada (hoy)'],
            ['value' => 'rechazada', 'label' => 'Marcar como Rechazada'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/shared/tables.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/invoices/invoices/index.js') }}"></script>
@endpush
