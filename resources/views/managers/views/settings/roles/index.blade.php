@extends('layouts.managers')

@section('title', 'Roles y permisos')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/roles/index.css') }}">
@endpush

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.roles.matrix') }}" class="btn btn-outline-secondary">Matriz de permisos</a>
                        <a href="{{ route('manager.roles.create') }}" class="btn btn-primary">Crear rol</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Roles del sistema',
        'description' => 'Administra los roles y sus permisos',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div id="rolesPage" class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-action-url="{{ route('manager.roles.bulk-action') }}">

                <div id="ajax-table-root">
            @include('managers.views.settings.roles._table')
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'rol(es)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/roles/index.js') }}"></script>
@endpush
