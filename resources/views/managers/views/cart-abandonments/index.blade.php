@extends('layouts.managers')

@section('title', 'Carritos incompletos')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Carritos incompletos',
        'description' => 'Correos capturados en el checkout (autenticados o invitados) que todavía no generaron una orden.
                        Se recuerdan automáticamente por correo una hora después.',
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list" id="cart-abandonments-index"
         data-config='@json([
            "routes" => [
                "bulkAction" => route("manager.cart-abandonments.bulk-action"),
            ],
         ])'>

                <div id="ajax-table-root">
            @include('managers.views.cart-abandonments._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'registro(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/cart-abandonments/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/cart-abandonments/index.js') }}"></script>
@endpush
