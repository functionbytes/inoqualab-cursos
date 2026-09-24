@extends('layouts.managers')

@section('title', 'Usuarios')

@section('page_header')
    @php ob_start(); @endphp
    <div class="btn-group">
        <button type="button" class="btn btn-icon btn-actions-icon dropdown-toggle arrow-none"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Acciones">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('manager.enterprises.users.reports', $enterprise->slack) }}">
                Reporte
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('manager.enterprises.users.create', $enterprise->slack) }}">
                Nuevo usuario
            </a>
        </div>
    </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Usuarios de la empresa',
        'description' => 'Gestiona los usuarios asignados a esta empresa',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    <div class="widget-content searchable-container list" id="enterprise-users-page"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-url="{{ route('manager.enterprises.users.bulk-action', $enterprise->slack) }}">

                <div id="ajax-table-root">
            @include('managers.views.enterprises.users._table')
        </div>
    </div>

    

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'usuario(s)',
        'bulkActions' => [
            ['value' => 'activate', 'label' => 'Activar'],
            ['value' => 'deactivate', 'label' => 'Desactivar'],
        ],
    ])

    @include('managers.includes.delete')

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/enterprises/users/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/enterprises/users/index.js') }}"></script>
@endpush
