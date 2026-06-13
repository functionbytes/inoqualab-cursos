@extends('layouts.managers')

@section('title', $role ? 'Editar rol' : 'Crear rol')

@php
    $isProtected = $role && in_array($role->name, $protectedRoles, true);
    $grouped = $permissions->groupBy(fn ($perm) => explode('.', $perm->name)[0]);
    $assignedCount = count($rolePermissionIds);
@endphp

@section('content')

    <form method="POST"
          action="{{ $role ? route('manager.roles.update', $role->id) : route('manager.roles.store') }}">
        @csrf
        @if($role)
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-header border-bottom p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">{{ $role ? 'Editar rol: '.$role->name : 'Nuevo rol' }}</h5>
                        <p class="small mb-0 text-muted">Define el nombre y los permisos asignados al rol.</p>
                    </div>
                    <a href="{{ route('manager.roles.index') }}" class="btn btn-outline-secondary">Volver</a>
                </div>
            </div>

            <div class="card-body border-bottom">
                @if($isProtected)
                    <div class="alert alert-warning border-0">
                        <i class="fas fa-triangle-exclamation me-2"></i>
                        <strong>Rol del sistema:</strong> el nombre no se puede cambiar, pero sí sus permisos.
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del rol <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name"
                               value="{{ old('name', $role->name ?? '') }}"
                               placeholder="ej: supervisor-cursos"
                               {{ $isProtected ? 'readonly' : 'required' }}>
                        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Guard</label>
                        <input type="text" class="form-control" value="web" readonly>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card bg-light-secondary h-100 mb-0">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-2">Disponibles</h6>
                                <h4 class="mb-1 fw-bold">{{ $permissions->count() }}</h4>
                                <small class="text-muted">Permisos del sistema</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-secondary h-100 mb-0">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Asignados</h6>
                                <h4 class="mb-1 fw-bold" id="assignedCount">{{ $assignedCount }}</h4>
                                <small class="text-muted">Permisos activos del rol</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light-secondary h-100 mb-0">
                            <div class="card-body">
                                <h6 class="card-title text-info mb-2">Grupos</h6>
                                <h4 class="mb-1 fw-bold">{{ $grouped->count() }}</h4>
                                <small class="text-muted">Módulos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Permission matrix --}}
            <div class="card-body">
                <div class="mb-3">
                    <input type="search" id="permissionFilter" class="form-control"
                           placeholder="Filtrar permisos por nombre...">
                </div>

                <div id="permissionsContainer">
                    @foreach($grouped as $group => $groupPermissions)
                        <div class="card shadow-sm mb-3 perm-group">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <strong class="text-uppercase">{{ ucwords(str_replace(['_', '-'], ' ', $group)) }}</strong>
                                <div class="form-check mb-0">
                                    <input class="form-check-input group-toggle" type="checkbox" id="group_{{ $group }}">
                                    <label class="form-check-label small text-muted" for="group_{{ $group }}">Todos</label>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($groupPermissions as $perm)
                                        <div class="col-md-4 perm-item">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input permission-checkbox" type="checkbox"
                                                       id="permission_{{ $perm->id }}" name="permissions[]"
                                                       value="{{ $perm->id }}"
                                                       {{ in_array($perm->id, $rolePermissionIds) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $perm->id }}">
                                                    {{ ucwords(str_replace(['.', '_'], ' ', $perm->name)) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary w-100 mb-2">
                    {{ $role ? 'Guardar cambios' : 'Crear rol' }}
                </button>
                <a href="{{ route('manager.roles.index') }}" class="btn btn-light w-100">Cancelar</a>
            </div>
        </div>
    </form>

@endsection

@push('scripts')
<script>
$(function () {
    function refreshAssigned() {
        $('#assignedCount').text($('.permission-checkbox:checked').length);
    }

    // Filtro por nombre
    $('#permissionFilter').on('input', function () {
        const term = $(this).val().toLowerCase();
        $('.perm-item').each(function () {
            const label = $(this).find('.form-check-label').text().toLowerCase();
            $(this).toggle(term === '' || label.includes(term));
        });
        $('.perm-group').each(function () {
            $(this).toggle($(this).find('.perm-item:visible').length > 0);
        });
    });

    // Marcar/desmarcar todo un grupo
    $('.group-toggle').on('change', function () {
        $(this).closest('.perm-group').find('.permission-checkbox').prop('checked', this.checked);
        refreshAssigned();
    });

    $('.permission-checkbox').on('change', refreshAssigned);

    // Estado inicial de los toggles de grupo
    $('.perm-group').each(function () {
        const total = $(this).find('.permission-checkbox').length;
        const checked = $(this).find('.permission-checkbox:checked').length;
        $(this).find('.group-toggle').prop('checked', total > 0 && total === checked);
    });

    @if(session('success'))
        toastr.success(@json(session('success')), 'Éxito');
    @endif
});
</script>
@endpush
