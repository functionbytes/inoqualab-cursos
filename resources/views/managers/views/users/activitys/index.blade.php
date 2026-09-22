@extends('layouts.managers')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Actividades - ' . $user->firstname.' '.$user->lastname,
        'description' => 'Historial de auditoría de acciones realizadas por este usuario',
    ])
@endsection

@section('content')

    @php
        $total = array_sum($counts);
    @endphp

    <div class="widget-content searchable-container list">

                <div id="ajax-table-root">
            @include('managers.views.users.activitys._table')
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/users/activitys/index.js') }}"></script>
@endpush
