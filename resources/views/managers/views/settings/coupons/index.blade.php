@extends('layouts.managers')

@section('title', 'Cupones')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/coupons/index.css') }}">
@endpush

@section('page_header')
    @php ob_start(); @endphp
@can('coupons.create')
                        <a href="{{ route('manager.coupons.create') }}" class="btn btn-primary btn-icon" title="Nuevo cupón" aria-label="Nuevo cupón">{!! \App\Html\IconHelper::render('plus') !!}</a>
                        @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Cupones de descuento',
        'description' => 'Gestiona los cupones de descuento de la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div id="couponsPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-bulk-action-url="{{ route('manager.coupons.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.coupons._table')
        </div>
    </div>

    

    @include('managers.includes.delete')

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'cupón(es)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/coupons/index.js') }}"></script>
@endpush
