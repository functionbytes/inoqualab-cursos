<div class="card">

            {{-- Header --}}
            

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if (($role ?? '') !== '') {
                        $__popoverLabels_Role = ['manager' => 'Administrador', 'customer' => 'Cliente', 'enterprise' => 'Empresa', 'distributor' => 'Empleado distribuidor', 'support' => 'Soporte', 'accounting' => 'Contabilidad'];
                        $filterChips[] = [
                            'label' => 'Perfil: ' . ($__popoverLabels_Role[$role ?? ''] ?? ($role ?? '')),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('role')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('manager.users') }}" id="searchForm">

                    <input type="hidden" name="role" id="filterRole" value="{{ $role ?? '' }}">

                    @php ob_start(); @endphp
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Perfil</div>
                    <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Role" value="" {{ ($role ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos los perfiles</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Role" value="manager" {{ ($role ?? '') === 'manager'     ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Administrador</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Role" value="customer" {{ ($role ?? '') === 'customer'    ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Cliente</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Role" value="enterprise" {{ ($role ?? '') === 'enterprise'  ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Empresa</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Role" value="distributor" {{ ($role ?? '') === 'distributor' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Empleado distribuidor</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Role" value="support" {{ ($role ?? '') === 'support'     ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Soporte</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Role" value="accounting" {{ ($role ?? '') === 'accounting'  ? 'checked' : '' }}>
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

            @include('managers.includes.pagination-footer', [
                'paginator' => $users,
                'itemLabel' => 'usuarios',
            ])

        </div>
