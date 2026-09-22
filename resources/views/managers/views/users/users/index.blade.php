@extends('layouts.managers')

@section('title', 'Usuarios')

@section('page_header')
    @php ob_start(); @endphp
@can('users.create')
                        <a href="{{ route('manager.users.create') }}" class="btn btn-primary btn-icon" title="Nuevo usuario" aria-label="Nuevo usuario">{!! \App\Html\IconHelper::render('plus') !!}</a>
                        @endcan
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Usuarios',
        'description' => 'Gestiona los usuarios registrados en la plataforma',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="users-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-config='@json([
            "routes" => [
                "bulkAction" => route("manager.users.bulk-action"),
            ],
         ])'>

                <div id="ajax-table-root">
            @include('managers.views.users.users._table')
        </div>
    </div>

    

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'usuario(s)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/users/users/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/users/users/index.js') }}"></script>
@endpush
