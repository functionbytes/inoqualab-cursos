@extends('layouts.managers')

@section('title', 'Roles y permisos')

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Roles del sistema</h5>
                        <p class="small mb-0 text-muted">Administra los roles y sus permisos</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('manager.roles.create') }}" class="btn btn-primary">Crear rol</a>
                    </div>
                </div>
            </div>

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
                <form action="{{ route('manager.roles.index') }}" method="GET">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-magnifying-glass text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por nombre..." value="{{ $searchKey ?? '' }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Buscar</button>
                        @if($searchKey)
                            <a href="{{ route('manager.roles.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($roles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
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
                        <div class="mb-3"><i class="fas fa-shield-halved text-muted" style="font-size:2.5rem;"></i></div>
                        <h6 class="mb-1">No hay roles {{ $searchKey ? 'que coincidan' : 'configurados' }}</h6>
                        <p class="text-muted mb-3">Crea el primer rol para gestionar permisos.</p>
                        <a href="{{ route('manager.roles.create') }}" class="btn btn-sm btn-primary">Crear rol</a>
                    </div>
                @endif
            </div>

            {{-- Pagination --}}
            @if($roles->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $roles->firstItem() }}–{{ $roles->lastItem() }} de {{ $roles->total() }} roles
                    </span>
                    {{ $roles->withQueryString()->links() }}
                </div>
            @endif

        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(document).on('click', '.delete-btn', function () {
        $('#delete-form').attr('action', $(this).data('url'));
    });

    @if(session('success'))
        toastr.success(@json(session('success')), 'Éxito');
    @endif
    @if(session('error'))
        toastr.error(@json(session('error')), 'Error');
    @endif
</script>
@endpush
