@extends('layouts.managers')

@section('title', 'Matriz de permisos')

@push('css')
<style>
    .perm-matrix { border-collapse: separate; border-spacing: 0; }
    .perm-matrix thead th { position: sticky; top: 0; z-index: 3; background: #f8f9fa; }
    .perm-matrix th.col-perm,
    .perm-matrix td.col-perm {
        position: sticky; left: 0; z-index: 2; background: #fff;
        min-width: 240px; max-width: 240px; border-right: 1px solid #e9ecef;
    }
    .perm-matrix thead th.col-perm { z-index: 4; background: #f8f9fa; }
    .perm-matrix .col-role { min-width: 92px; text-align: center; }
    .perm-matrix tr.module-row td { background: #eef2f7; font-weight: 600; }
    .perm-matrix td.col-perm.small-name { font-size: .8rem; }
    .perm-has { color: #13C672; }
    .perm-not { color: #dfe3e8; }
    .matrix-scroll { max-height: 70vh; }
    .badge-mini { font-size: .6rem; }
</style>
@endpush

@section('content')

    <div class="card">
        <div class="card-header p-4 border-bottom border-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold">Matriz de permisos por rol</h5>
                    <p class="small mb-0 text-muted">Qué puede hacer cada rol (✓) y qué no (–), de un vistazo</p>
                </div>
                <a href="{{ route('manager.roles.index') }}" class="btn btn-outline-secondary">Atrás</a>
            </div>
        </div>

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
<script>
    $('#matrixFilter').on('input', function () {
        const term = $(this).val().toLowerCase();
        $('.perm-row').each(function () {
            const name = ($(this).data('name') + '').toLowerCase();
            $(this).toggle(term === '' || name.includes(term));
        });
        // Oculta el encabezado de módulo si no le quedan permisos visibles
        $('.module-row').each(function () {
            let next = $(this).next('.perm-row');
            let anyVisible = false;
            while (next.length) {
                if (next.is(':visible')) { anyVisible = true; break; }
                next = next.next('.perm-row');
            }
            $(this).toggle(anyVisible || term === '');
        });
    });
</script>
@endpush
