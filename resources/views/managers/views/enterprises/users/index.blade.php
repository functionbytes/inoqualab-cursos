@extends('layouts.managers')

@section('title', 'Usuarios')

@section('content')


    <div class="widget-content searchable-container list" id="enterprise-users-page"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-bulk-url="{{ route('manager.enterprises.users.bulk-action', $enterprise->slack) }}">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Usuarios de la empresa</h5>
                        <p class="mb-0 text-muted">Gestiona los usuarios asignados a esta empresa</p>
                    </div>
                    <div class="d-flex gap-2 ms-auto">
                        <a href="{{ route('manager.enterprises.users.reports', $enterprise->slack) }}" class="btn btn-outline-secondary">
                            Reporte
                        </a>
                        <a href="{{ route('manager.enterprises.users.create', $enterprise->slack) }}" class="btn btn-primary">
                            Nuevo usuario
                        </a>
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">

                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por nombre, correo o identificación..."
                                       value="{{ $searchKey ?? '' }}">
                            </div>
                        </div>

                        @php
                            $activeFilters = (int)(($available ?? '') !== '');
                        @endphp
                        <button type="button" class="btn btn-outline-secondary flex-shrink-0" title="Filtros"
                                data-bs-toggle="modal" data-bs-target="#filters-modal">
                            <i class="fas fa-sliders"></i>
                            @if($activeFilters > 0)
                                <span class="badge bg-primary ms-1">{{ $activeFilters }}</span>
                            @endif
                        </button>

                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Identificación</th>
                                    <th>Cliente</th>
                                    <th>Correo electrónico</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $user->id }}">
                                        </td>
                                        <td>{{ $user->identification }}</td>
                                        <td>
                                            <div class="fw-semibold">
                                                {{ Str::words(Str::upper(Str::lower($user->firstname . ' ' . $user->lastname)), 12, '...') }}
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($user->updated_at)) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.enterprises.users.courses', $user->slack) }}">
                                                            Cursos
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.users.certificates', $user->slack) }}">
                                                            Certificados
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.enterprises.users.edit', $user->slack) }}">
                                                            Gestionar
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay usuarios
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') || ($available ?? '') !== '')
                                No hay usuarios que coincidan con los filtros aplicados.
                            @else
                                Agrega el primer usuario a esta empresa.
                            @endif
                        </p>
                        @if(($searchKey ?? '') || ($available ?? '') !== '')
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            <a href="{{ route('manager.enterprises.users.create', $enterprise->slack) }}" class="btn btn-primary">
                                Nuevo usuario
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if($users->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }} usuarios
                    </span>
                    {{ $users->appends(request()->input())->links() }}
                </div>
            @endif

        </div>
    </div>

    {{-- Filters modal --}}
    <div class="modal fade" id="filters-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filtros avanzados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Estado</label>
                        <select id="modalAvailable" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" {{ ($available ?? '') === '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ ($available ?? '') === '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ Request::url() }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
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
