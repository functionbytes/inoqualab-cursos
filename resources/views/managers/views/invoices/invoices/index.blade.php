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
         data-flash-error="{{ session('error') }}">

                <div id="ajax-table-root">
            @include('managers.views.invoices.invoices._table')
        </div>
    </div>

    

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/invoices/invoices/index.js') }}"></script>
@endpush
