@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', [
        'title' => 'Cursos inscritos' . (isset($user) ? ' - '.$user->firstname.' '.$user->lastname : ''),
        'description' => 'Cursos en los que este usuario está inscrito',
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-bulk-url="{{ route('support.users.courses.bulk-action') }}"
         data-bulk-entity-label="inscripción(es)">

        <div id="ajax-table-root">
            @include('supports.views.users.courses._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'inscripción(es)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/users/courses/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('supports/js/views/users/courses/index.js') }}"></script>
@endpush
