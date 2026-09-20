@extends('layouts.managers')

@section('title', 'Usuarios')

@section('content')


    <div class="widget-content searchable-container list" id="users-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}"
         data-config='@json([
            "routes" => [
                "bulkAction" => route("manager.users.bulk-action"),
            ],
         ])'>

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Usuarios</h5>
                        <p class="mb-0 text-muted">Gestiona los usuarios registrados en la plataforma</p>
                    </div>
                    <div class="ms-auto">
                        @can('users.create')
                        <a href="{{ route('manager.users.create') }}" class="btn btn-primary">
                            Nuevo usuario
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('manager.users') }}" id="searchForm">

                    <input type="hidden" name="role" id="filterRole" value="{{ $role ?? '' }}">

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
                            $activeFilters = (int)(($role ?? '') !== '');
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
                                    <th class="users-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Identificación</th>
                                    <th>Cliente</th>
                                    <th>Correo electrónico</th>
                                    <th class="text-center">Perfil</th>
                                    <th class="text-center">Fecha creación</th>
                                    <th class="text-center">Actualización</th>
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
                                                {{ Str::words(Str::upper(Str::lower($user->firstname . ' ' . $user->lastname)), 2, '...') }}
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td class="text-center">
                                            @php
                                                $roleLabels = [
                                                    'manager'     => 'Administrador',
                                                    'customer'    => 'Cliente',
                                                    'enterprise'  => 'Empresa',
                                                    'distributor' => 'Empleado distribuidor',
                                                    'support'     => 'Soporte',
                                                    'accounting'  => 'Contabilidad',
                                                ];
                                            @endphp
                                            <span class="badge bg-primary-subtle text-primary">
                                                {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($user->created_at)) }}</span>
                                        </td>
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
                                                    @if($user->role === 'customer')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('manager.users.results', $user->slack) }}">
                                                                Resultados
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('manager.users.certificates', $user->slack) }}">
                                                                Certificados
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('manager.users.orders', $user->slack) }}">
                                                                Ordenes
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('manager.users.inscriptions', $user->slack) }}">
                                                                Inscripciones
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                    @endif
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.users.emails', $user->slack) }}">
                                                            Correos enviados
                                                        </a>
                                                    </li>
                                                    @can('users.update')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.users.edit', $user->slack) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    @endcan
                                                    @can('users.delete')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.users.destroy', $user->slack) }}"
                                                           data-title="Eliminar: {{ $user->firstname }} {{ $user->lastname }}">
                                                            Eliminar
                                                        </a>
                                                    </li>
                                                    @endcan
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
                            @if(($searchKey ?? '') || ($role ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay usuarios
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') || ($role ?? '') !== '')
                                No hay usuarios que coincidan con los filtros aplicados.
                            @else
                                Los usuarios registrados aparecerán aquí.
                            @endif
                        </p>
                        @if(($searchKey ?? '') || ($role ?? '') !== '')
                            <a href="{{ route('manager.users') }}" class="btn btn-outline-secondary">
                                Ver todos
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
                        <label class="form-label fw-semibold">Perfil</label>
                        <select id="modalRole" class="form-select">
                            <option value="">Todos los perfiles</option>
                            <option value="manager"     {{ ($role ?? '') === 'manager'     ? 'selected' : '' }}>Administrador</option>
                            <option value="customer"    {{ ($role ?? '') === 'customer'    ? 'selected' : '' }}>Cliente</option>
                            <option value="enterprise"  {{ ($role ?? '') === 'enterprise'  ? 'selected' : '' }}>Empresa</option>
                            <option value="distributor" {{ ($role ?? '') === 'distributor' ? 'selected' : '' }}>Empleado distribuidor</option>
                            <option value="support"     {{ ($role ?? '') === 'support'     ? 'selected' : '' }}>Soporte</option>
                            <option value="accounting"  {{ ($role ?? '') === 'accounting'  ? 'selected' : '' }}>Contabilidad</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ route('manager.users') }}" class="btn btn-secondary w-100">
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
