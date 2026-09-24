<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted">Distribuidores registrados</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Publicos</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['public']) }}</h4>
                        <span class="text-muted">Visibles</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Ocultos</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['hidden']) }}</h4>
                        <span class="text-muted">No visibles</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Generan empresas</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['enterprise_generate']) }}</h4>
                        <span class="text-muted">Con permiso habilitado</span>
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
                $__popoverLabels_Available = ['1' => 'Publico', '0' => 'Oculto'];
                $filterChips[] = [
                    'label' => 'Estado: ' . ($__popoverLabels_Available[$available ?? ''] ?? ($available ?? '')),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('available')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('support.distributors') }}" id="searchForm">

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
                <span>Publico</span>
            </label>
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Available" value="0" {{ ($available ?? '') === '0' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Oculto</span>
            </label>
            </div>
        </div>
            @php $popoverBody = trim(ob_get_clean()); @endphp

            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => $searchKey ?? '',
                'searchPlaceholder' => 'Buscar por título, NIT o correo...',
                'popoverBody' => $popoverBody,
                'filterChips' => $filterChips,
            ])
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card-body">
        @if($distributors->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="distributors-col-checkbox"><input type="checkbox" class="form-check-input" id="select-all"></th>
                            <th>Título</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Actualización</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($distributors as $distributor)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $distributor->id }}">
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($distributor->title)), 12, '...') }}</div>
                                </td>
                                <td class="text-center">
                                    @if($distributor->available == 1)
                                        <span class="badge bg-success-subtle text-success">Publico</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="text-muted">{{ $distributor->updated_at->format('d/m/Y') }}</span>
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
                                                <a class="dropdown-item" href="{{ route('support.distributors.navegation', $distributor->slack) }}">
                                                    Dashboard
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('support.distributors.view', $distributor->slack) }}">
                                                    Ver
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('support.distributors.edit', $distributor->slack) }}">
                                                    Editar
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item confirm-delete" href="#"
                                                   data-href="{{ route('support.distributors.destroy', $distributor->slack) }}">
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-building', 48) !!}</div>
                <h5 class="fw-bold mb-2">
                    @if(($searchKey ?? '') || ($available ?? '') !== '')
                        No se encontraron resultados
                    @else
                        No hay distribuidores
                    @endif
                </h5>
                <p class="text-muted mb-4">
                    @if(($searchKey ?? '') || ($available ?? '') !== '')
                        No hay distribuidores que coincidan con los filtros aplicados.
                    @else
                        Crea el primer distribuidor de la plataforma.
                    @endif
                </p>
                @if(($searchKey ?? '') || ($available ?? '') !== '')
                    <a href="{{ route('support.distributors') }}" class="btn btn-outline-secondary">
                        Ver todos
                    </a>
                @else
                    <a href="{{ route('support.distributors.create') }}" class="btn btn-primary">
                        Nuevo distribuidor
                    </a>
                @endif
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $distributors,
        'itemLabel' => 'distribuidores',
    ])

</div>
