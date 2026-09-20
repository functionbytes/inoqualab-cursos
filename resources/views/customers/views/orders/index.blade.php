@extends('layouts.customers')

@section('title', 'Mis pedidos')

@section('context-title', 'Mis pedidos')
@section('context-icon')@include('customers.includes.icon', ['name' => 'receipt'])@endsection
@section('context-subtitle', 'Revisa tus compras, facturas y su estado de pago')
@section('context-stat-number', $orders->total())
@section('context-stat-label', Str::plural('pedido', $orders->total()))

@section('content')
<section class="pnl-section" id="ordersContent">
    @include('customers.partials.views.orders.list')
</section>
@endsection

@push('scripts')
<script src="{{ asset('customers/js/views/orders/index.js') }}"></script>
@endpush
