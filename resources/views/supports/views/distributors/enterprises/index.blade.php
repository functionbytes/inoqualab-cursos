@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.distributors.enterprises.assignments', $distributor->slack) }}"
                           class="btn btn-outline-secondary" title="Reasignar empresas">
                            <i class="fa-duotone fa-solid fa-right-left"></i>
                        </a>
                        <a href="{{ route('support.distributors.orders.reports', $distributor->slack) }}"
                           class="btn btn-outline-secondary" title="Reporte">
                            <i class="fa-solid fa-file-chart-column"></i>
                        </a>
                        <a href="{{ route('support.distributors.enterprises.create', $distributor->slack) }}" class="btn btn-primary btn-icon" title="Nueva empresa" aria-label="Nueva empresa">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Empresas de ' . $distributor->title,
        'description' => 'Gestiona las empresas asignadas a este distribuidor',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-bulk-url="{{ route('support.distributors.enterprises.bulk-action') }}"
         data-bulk-entity-label="empresa(s)">

        <div id="ajax-table-root">
            @include('supports.views.distributors.enterprises._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'empresa(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('supports/css/views/distributors/enterprises/index.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('supports/js/views/distributors/enterprises/index.js') }}"></script>
@endpush
