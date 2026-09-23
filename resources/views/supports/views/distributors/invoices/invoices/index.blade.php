@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.distributors.invoices.report') }}" class="btn btn-primary">
                            Reporte
                        </a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Facturación de ' . $distributor->title,
        'description' => 'Consulta las facturas emitidas a este distribuidor',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}">

        <div id="ajax-table-root">
            @include('supports.views.distributors.invoices.invoices._table')
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('supports/js/views/distributors/invoices/invoices/index.js') }}"></script>
@endpush
