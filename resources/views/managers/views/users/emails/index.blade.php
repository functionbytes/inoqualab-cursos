@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
    <div class="btn-group">
        <button type="button" class="btn btn-icon btn-actions-icon dropdown-toggle arrow-none"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Acciones">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <a href="{{ route('manager.users.edit', $user->slack) }}" class="dropdown-item">Volver al usuario</a>
        </div>
    </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Historial de correos',
        'description' => 'Todos los correos electrónicos enviados a ' . $user->firstname . ' ' . $user->lastname . ' (' . $user->email . ')',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


        <div id="ajax-table-root">
        @include('managers.views.users.emails._table')
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/users/emails/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/users/emails/index.js') }}"></script>
@endpush
