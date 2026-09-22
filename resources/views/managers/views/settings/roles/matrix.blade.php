@extends('layouts.managers')

@section('title', 'Matriz de permisos')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/roles/matrix.css') }}">
@endpush

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Matriz de permisos por rol',
        'description' => 'Qué puede hacer cada rol (✓) y qué no (–), de un vistazo',
    ])
@endsection

@section('content')

    <div class="card">
        

        {{-- Stats + leyenda --}}
        <div class="card-body border-bottom d-flex flex-wrap gap-4 align-items-center">
            <div><span class="h4 fw-bold mb-0">{{ $roles->count() }}</span> <span class="text-muted">roles</span></div>
            <div><span class="h4 fw-bold mb-0">{{ $totalPermissions }}</span> <span class="text-muted">permisos</span></div>
            <div class="ms-auto small text-muted">
                <i class="fas fa-check perm-has"></i> tiene el permiso &nbsp;·&nbsp;
                <i class="fas fa-minus perm-not"></i> no lo tiene
            </div>
        </div>

        <div class="card-body border-bottom">
            <input type="search" id="matrixFilter" class="form-control" placeholder="Filtrar permisos por nombre...">
        </div>

        {{-- Grilla --}}
        <div class="card-body p-0">
            <div class="table-responsive matrix-scroll">
                <table class="table table-hover table-sm mb-0 perm-matrix">
                    <thead>
                        <tr>
                            <th class="col-perm">Permiso</th>
                            @foreach($roles as $role)
                                <th class="col-role">
                                    <div class="fw-semibold text-capitalize">{{ $role->name }}</div>
                                    @if(in_array($role->name, $protectedRoles, true))
                                        <span class="badge bg-dark badge-mini">sistema</span>
                                    @endif
                                    <div class="small text-muted">{{ $role->permissions->count() }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissionsByModule as $module => $permissions)
                            <tr class="module-row">
                                <td class="col-perm">{{ ucwords(str_replace(['_', '-'], ' ', $module)) }}</td>
                                @foreach($roles as $role)
                                    <td class="col-role text-muted small">
                                        {{ $permissions->filter(fn ($p) => $rolePermissionIds[$role->id]->has($p->id))->count() }}/{{ $permissions->count() }}
                                    </td>
                                @endforeach
                            </tr>
                            @foreach($permissions as $perm)
                                <tr class="perm-row" data-name="{{ $perm->name }}">
                                    <td class="col-perm small-name">{{ ucwords(str_replace(['.', '_'], ' ', $perm->name)) }}</td>
                                    @foreach($roles as $role)
                                        <td class="col-role">
                                            @if($rolePermissionIds[$role->id]->has($perm->id))
                                                <i class="fas fa-check perm-has"></i>
                                            @else
                                                <i class="fas fa-minus perm-not"></i>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/roles/matrix.js') }}"></script>
@endpush
