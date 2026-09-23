@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.users.create') }}" class="btn btn-primary btn-icon" title="Nuevo usuario" aria-label="Nuevo usuario">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Usuarios',
        'description' => 'Gestiona los usuarios registrados en la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-bulk-url="{{ route('support.users.bulk-action') }}"
         data-bulk-entity-label="usuario(s)">

        <div id="ajax-table-root">
            @include('supports.views.users.users._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'usuario(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('supports/css/views/users/users/index.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('supports/js/views/users/users/index.js') }}"></script>
@endpush
