@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.enterprises.courses.assign', $enterprise->slack) }}" class="btn btn-primary">
                            Asignar cursos
                        </a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Cursos',
        'description' => 'Cursos asignados a ' . Str::words(Str::upper(Str::lower($enterprise->title)), 8, '...'),
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div id="ajax-table-root">
            @include('supports.views.enterprises.courses._table')
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('supports/js/enterprises/courses/index.js') }}"></script>
@endpush
