@extends('layouts.managers')

@section('title', 'Órdenes del usuario')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Órdenes del usuario'])
@endsection

@section('content')


    <div class="widget-content searchable-container list">

        <div id="ajax-table-root">
            @include('managers.views.users.users._orders', ['orders' => $orders, 'searchKey' => $searchKey ?? null])
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/users/users/orders.js') }}"></script>
@endpush




