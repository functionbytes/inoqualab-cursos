@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', [
        'title' => 'Cursos',
        'description' => 'Cursos de ' . $user->firstname . ' ' . $user->lastname,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div id="ajax-table-root">
            @include('supports.views.enterprises.users.courses._table')
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('supports/js/enterprises/users/courses/index.js') }}"></script>
@endpush
