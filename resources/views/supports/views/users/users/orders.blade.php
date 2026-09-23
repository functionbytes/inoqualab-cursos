@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', [
        'title' => 'Ordenes - ' . $user->firstname . ' ' . $user->lastname,
        'description' => 'Ordenes de compra registradas por este usuario',
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-bulk-url="{{ route('support.users.orders.bulk-action') }}"
         data-bulk-entity-label="orden(es)">

        <div id="ajax-table-root">
            @include('supports.views.users.users._orders_table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'orden(es)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('supports/css/views/users/users/orders.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('supports/js/views/users/users/orders.js') }}"></script>
@endpush
