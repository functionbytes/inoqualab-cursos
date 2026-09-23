@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', [
        'title' => 'Resultados',
        'description' => 'Resultados de ' . $user->firstname . ' ' . $user->lastname,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div id="ajax-table-root">
            @include('supports.views.enterprises.users.results._table')
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('supports/js/enterprises/users/results/index.js') }}"></script>
@endpush
