@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
    <div class="btn-group">
        <button type="button" class="btn btn-icon btn-actions-icon dropdown-toggle arrow-none"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Acciones">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('support.enterprises.courses.assign', $enterprise->slack) }}">
                Asignar cursos
            </a>
        </div>
    </div>
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
