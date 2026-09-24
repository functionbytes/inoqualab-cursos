<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted">Empleados</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Activos</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['active']) }}</h4>
                        <span class="text-muted">Habilitados</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Inactivos</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['inactive']) }}</h4>
                        <span class="text-muted">Deshabilitados</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search + Filtros --}}
    <div class="card-body border-bottom">
        @php
            $filterChips = [];
            if (($available ?? '') !== '') {
                $__popoverLabels_Available = ['1' => 'Activo', '0' => 'Inactivo'];
                $filterChips[] = [
                    'label' => 'Estado: ' . ($__popoverLabels_Available[$available ?? ''] ?? ($available ?? '')),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('available')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('support.enterprises.staffs', $enterprise->slack) }}" id="searchForm"
              data-bulk-url="{{ route('support.enterprises.staffs.bulk-action') }}">

            <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">

            @php ob_start(); @endphp
        <div class="filter-popover-field">
            <div class="filter-popover-label">Estado</div>
            <div class="filter-popover-options">
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Available" value="" {{ ($available ?? '') === '' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Todos</span>
            </label>
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Available" value="1" {{ ($available ?? '') === '1' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Activo</span>
            </label>
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Available" value="0" {{ ($available ?? '') === '0' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Inactivo</span>
            </label>
            </div>
        </div>
            @php $popoverBody = trim(ob_get_clean()); @endphp

            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => $searchKey ?? '',
                'searchPlaceholder' => 'Buscar por nombre, correo o identificacion...',
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
                            <th class="col-checkbox">
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                            <th>Identificacion</th>
                            <th>Empleado</th>
                            <th>Correo electronico</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Actualizacion</th>
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
                                <td>{{ ucfirst($user->identification) }}</td>
                                <td>
                                    <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($user->firstname.' '.$user->lastname)), 12, '...') }}</div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td class="text-center">
                                    @if($user->available)
                                        <span class="badge bg-success-subtle text-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                                    @endif
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
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('support.enterprises.staffs.edit', $user->slack) }}">
                                                    Editar
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item confirm-delete"
                                                   href="#"
                                                   data-href="{{ route('support.enterprises.staffs.destroy', $user->slack) }}">
                                                    Eliminar
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-users', 48) !!}</div>
                <h5 class="fw-bold mb-2">
                    @if(($searchKey ?? '') || ($available ?? '') !== '')
                        No se encontraron resultados
                    @else
                        No hay empleados
                    @endif
                </h5>
                <p class="text-muted mb-4">
                    @if(($searchKey ?? '') || ($available ?? '') !== '')
                        No hay empleados que coincidan con los filtros aplicados.
                    @else
                        Crea el primer empleado de esta empresa.
                    @endif
                </p>
                @if(($searchKey ?? '') || ($available ?? '') !== '')
                    <a href="{{ route('support.enterprises.staffs', $enterprise->slack) }}" class="btn btn-outline-secondary">
                        Ver todos
                    </a>
                @else
                    <a href="{{ route('support.enterprises.staffs.create', $enterprise->slack) }}" class="btn btn-primary">
                        Nuevo empleado
                    </a>
                @endif
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $users,
        'itemLabel' => 'empleados',
    ])

</div>
