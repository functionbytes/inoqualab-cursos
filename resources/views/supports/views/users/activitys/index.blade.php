@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', [
        'title' => 'Actividades - ' . $user->firstname.' '.$user->lastname,
        'description' => 'Historial de auditoría de acciones realizadas por este usuario',
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div id="ajax-table-root">
            @include('supports.views.users.activitys._table')
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('supports/js/views/users/activitys/index.js') }}"></script>
@endpush
