<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted">Usuarios registrados</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Clientes</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['customers']) }}</h4>
                        <span class="text-muted">Rol cliente</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Empresas</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['enterprises']) }}</h4>
                        <span class="text-muted">Rol empresa</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Activos</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['actives']) }}</h4>
                        <span class="text-muted">Estado activo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search + Filtros --}}
    <div class="card-body border-bottom">
        @php
            $filterChips = [];
            if (($role ?? '') !== '') {
                $__popoverLabels_Role = [
                    'manager' => 'Administrador', 'customer' => 'Cliente', 'enterprise' => 'Empresa',
                    'distributor' => 'Empleado distribuidor', 'support' => 'Soporte', 'accounting' => 'Contabilidad',
                ];
                $filterChips[] = [
                    'label' => 'Perfil: ' . ($__popoverLabels_Role[$role ?? ''] ?? ($role ?? '')),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('role')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('support.users') }}" id="searchForm">

            <input type="hidden" name="role" id="filterRole" value="{{ $role ?? '' }}">

            @php ob_start(); @endphp
        <div class="filter-popover-field">
            <div class="filter-popover-label">Perfil</div>
            <div class="filter-popover-options">
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Role" value="" {{ ($role ?? '') === '' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Todos</span>
            </label>
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Role" value="manager" {{ ($role ?? '') === 'manager' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Administrador</span>
            </label>
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Role" value="customer" {{ ($role ?? '') === 'customer' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Cliente</span>
            </label>
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Role" value="enterprise" {{ ($role ?? '') === 'enterprise' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Empresa</span>
            </label>
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Role" value="distributor" {{ ($role ?? '') === 'distributor' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Empleado distribuidor</span>
            </label>
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Role" value="support" {{ ($role ?? '') === 'support' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Soporte</span>
            </label>
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Role" value="accounting" {{ ($role ?? '') === 'accounting' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Contabilidad</span>
            </label>
            </div>
        </div>
            @php $popoverBody = trim(ob_get_clean()); @endphp

            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => $searchKey ?? '',
                'searchPlaceholder' => 'Buscar por nombre, correo o identificación...',
                'popoverBody' => $popoverBody,
                'filterChips' => $filterChips,
            ])
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 users-table">
                    <thead class="table-light">
                        <tr>
                            <th class="users-col-checkbox">
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                            <th class="users-col-id">Identificación</th>
                            <th class="users-col-name">Cliente</th>
                            <th class="users-col-email">Correo electrónico</th>
                            <th class="text-center users-col-role">Perfil</th>
                            <th class="text-center users-col-updated">Actualización</th>
                            <th class="text-center users-col-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input bulk-checkbox"
                                           value="{{ $user->id }}">
                                </td>
                                <td>
                                    <span>{{ ucfirst($user->identification) }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-truncate">{{ Str::words(Str::upper(Str::lower($user->firstname.' '.$user->lastname)), 2, '...') }}</div>
                                </td>
                                <td>
                                    <span class="text-muted text-truncate d-block" title="{{ $user->email }}">{{ $user->email }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $user->role == 'manager' ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}">
                                        @if($user->role == 'manager')
                                            Administrador
                                        @elseif($user->role == 'customer')
                                            Cliente
                                        @elseif($user->role == 'enterprise')
                                            Empresa
                                        @elseif($user->role == 'distributor')
                                            Distribuidor
                                        @elseif($user->role == 'support')
                                            Soporte
                                        @elseif($user->role == 'accounting')
                                            Contabilidad
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="text-muted">{{ $user->updated_at->format('d/m/Y') }}</span>
                                </td>
                                <td class="text-center">
                                    @if($user->slack)
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                    data-bs-toggle="dropdown"
                                                    data-bs-boundary="viewport">
                                                <i class="fas fa-ellipsis-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @if($user->role == 'customer')
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('support.users.navegation', $user->slack) }}">
                                                            Dashboard
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <a class="dropdown-item"
                                                       href="{{ route('support.users.view', $user->slack) }}">
                                                        Visualizar
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                       href="{{ route('support.users.edit', $user->slack) }}">
                                                        Editar
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item confirm-delete"
                                                       href="#"
                                                       data-href="{{ route('support.users.destroy', $user->slack) }}">
                                                        Eliminar
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @else
                                        <span class="text-muted small" title="Registro sin identificador (slack) asignado">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-users', 48) !!}</div>
                <h5 class="fw-bold mb-2">
                    @if($searchKey || ($role ?? '') !== '')
                        No se encontraron resultados
                    @else
                        No hay usuarios
                    @endif
                </h5>
                <p class="text-muted mb-4">
                    @if($searchKey || ($role ?? '') !== '')
                        No hay usuarios que coincidan con los filtros aplicados.
                    @else
                        Crea el primer usuario de la plataforma.
                    @endif
                </p>
                @if($searchKey || ($role ?? '') !== '')
                    <a href="{{ route('support.users') }}" class="btn btn-outline-secondary">
                        Ver todos
                    </a>
                @else
                    <a href="{{ route('support.users.create') }}" class="btn btn-primary">
                        Nuevo usuario
                    </a>
                @endif
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $users,
        'itemLabel' => 'usuarios',
    ])

</div>
