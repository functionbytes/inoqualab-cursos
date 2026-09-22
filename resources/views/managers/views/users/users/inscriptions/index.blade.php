@extends('layouts.managers')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Inscripciones del usuario',
        'description' => 'Cursos en los que el usuario está inscrito',
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="users-inscriptions-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}">

                <div id="ajax-table-root">
            @include('managers.views.users.users.inscriptions._table')
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/users/users/inscriptions/index.js') }}"></script>
@endpush
