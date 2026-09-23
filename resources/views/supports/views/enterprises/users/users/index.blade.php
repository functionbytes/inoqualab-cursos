@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.enterprises.users.create', $enterprise->slack) }}" class="btn btn-primary btn-icon" title="Nuevo usuario" aria-label="Nuevo usuario">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Usuarios',
        'description' => 'Usuarios de ' . Str::words(Str::upper(Str::lower($enterprise->title)), 8, '...'),
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div id="ajax-table-root">
            @include('supports.views.enterprises.users.users._table')
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('supports/js/enterprises/users/users/index.js') }}"></script>
@endpush
