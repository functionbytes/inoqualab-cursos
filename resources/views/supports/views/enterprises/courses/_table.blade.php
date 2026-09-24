<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted">Cursos asignados</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Publicados</h6>
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
        <form method="GET" action="{{ route('support.enterprises.courses', $enterprise->slack) }}" id="searchForm">

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
                'searchPlaceholder' => 'Buscar por titulo...',
                'popoverBody' => $popoverBody,
                'filterChips' => $filterChips,
            ])
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card-body">
        @if($courses->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Titulo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $course)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($course->title)), 12, '...') }}</div>
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
                                                   href="{{ route('support.enterprises.courses.view', [$enterprise->slack, $course->slack]) }}">
                                                    Detalle
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-courses', 48) !!}</div>
                <h5 class="fw-bold mb-2">
                    @if(($searchKey ?? '') || ($available ?? '') !== '')
                        No se encontraron resultados
                    @else
                        No hay cursos asignados
                    @endif
                </h5>
                <p class="text-muted mb-4">
                    @if(($searchKey ?? '') || ($available ?? '') !== '')
                        No hay cursos que coincidan con los filtros aplicados.
                    @else
                        Asigna el primer curso a esta empresa.
                    @endif
                </p>
                @if(($searchKey ?? '') || ($available ?? '') !== '')
                    <a href="{{ route('support.enterprises.courses', $enterprise->slack) }}" class="btn btn-outline-secondary">
                        Ver todos
                    </a>
                @else
                    <a href="{{ route('support.enterprises.courses.assign', $enterprise->slack) }}" class="btn btn-primary">
                        Asignar cursos
                    </a>
                @endif
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $courses,
        'itemLabel' => 'cursos',
    ])

</div>
