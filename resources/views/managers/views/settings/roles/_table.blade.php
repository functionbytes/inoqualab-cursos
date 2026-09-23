<div class="card">

            {{-- Header --}}
            

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100 mb-0">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ $roles->total() }}</h4>
                                <small class="text-muted">Roles configurados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100 mb-0">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Del sistema</h6>
                                <h4 class="mb-1 fw-bold">{{ count($protectedRoles) }}</h4>
                                <small class="text-muted">Roles protegidos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100 mb-0">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Permisos</h6>
                                <h4 class="mb-1 fw-bold">{{ $totalPermissions }}</h4>
                                <small class="text-muted">Permisos disponibles</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-secondary h-100 mb-0">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Usuarios</h6>
                                <h4 class="mb-1 fw-bold">{{ $roles->sum('users_count') }}</h4>
                                <small class="text-muted">En esta página</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form action="{{ route('manager.roles.index') }}" method="GET" id="searchForm">
                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $searchKey ?? '',
                        'searchPlaceholder' => 'Buscar por nombre...',
                    ])
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($roles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="roles-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Nombre del rol</th>
                                    <th>Guard</th>
                                    <th class="text-center">Permisos</th>
                                    <th class="text-center">Usuarios</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                    @php $isSystem = in_array($role->name, $protectedRoles, true); @endphp
                                    <tr>
                                        <td>
                                            @unless($isSystem)
                                                <input type="checkbox" class="form-check-input bulk-checkbox"
                                                       value="{{ $role->id }}">
                                            @endunless
                                        </td>
                                        <td>
                                            <a href="{{ route('manager.roles.edit', $role->id) }}" class="text-decoration-none fw-semibold">
                                                {{ $role->name }}
                                            </a>
                                        </td>
                                        <td><span class="badge bg-light text-black">{{ $role->guard_name }}</span></td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary">{{ $role->permissions_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-black">{{ $role->users_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($isSystem)
                                                <span class="badge bg-dark">Sistema</span>
                                            @else
                                                <span class="badge bg-light text-black">Normal</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <a href="#" class="text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a href="{{ route('manager.roles.edit', $role->id) }}" class="dropdown-item">
                                                            Editar y permisos
                                                        </a>
                                                    </li>
                                                    @unless($isSystem)
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a href="javascript:void(0)" class="dropdown-item delete-btn"
                                                               data-bs-toggle="modal" data-bs-target="#delete-modal"
                                                               data-url="{{ route('manager.roles.destroy', $role->id) }}">
                                                                Eliminar
                                                            </a>
                                                        </li>
                                                    @endunless
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
                        <div class="mb-3"><i class="fas fa-shield-halved text-muted roles-empty-icon"></i></div>
                        <h6 class="mb-1">No hay roles {{ $searchKey ? 'que coincidan' : 'configurados' }}</h6>
                        <p class="text-muted mb-3">Crea el primer rol para gestionar permisos.</p>
                        <a href="{{ route('manager.roles.create') }}" class="btn btn-sm btn-primary">Crear rol</a>
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $roles,
                'itemLabel' => 'roles',
            ])

        </div>
