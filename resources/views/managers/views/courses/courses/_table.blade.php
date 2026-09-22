<div class="card">

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                                <span class="text-muted">Cursos configurados</span>
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
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Website</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['website']) }}</h4>
                                <span class="text-muted">En sitio público</span>
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
                        $__popoverLabels_Available = ['1' => 'Publicado', '0' => 'Oculto'];
                        $filterChips[] = [
                            'label' => 'Estado: ' . ($__popoverLabels_Available[$available ?? ''] ?? ($available ?? '')),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('available')),
                        ];
                    }
                    if (($website ?? '') !== '') {
                        $__popoverLabels_Website = ['1' => 'Website', '0' => 'Interno'];
                        $filterChips[] = [
                            'label' => 'Público: ' . ($__popoverLabels_Website[$website ?? ''] ?? ($website ?? '')),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('website')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('manager.courses') }}" id="searchForm">

                    <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">
                    <input type="hidden" name="website"   id="filterWebsite"   value="{{ $website ?? '' }}">

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
                        <span>Publicado</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Available" value="0" {{ ($available ?? '') === '0' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Oculto</span>
                    </label>
                    </div>
                </div>
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Público</div>
                    <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Website" value="" {{ ($website ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Website" value="1" {{ ($website ?? '') === '1' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Website</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Website" value="0" {{ ($website ?? '') === '0' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Interno</span>
                    </label>
                    </div>
                </div>
@php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $searchKey ?? '',
                        'searchPlaceholder' => 'Buscar por título...',
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
                                    <th class="courses-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Título</th>
                                    <th class="text-center">Público</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courses as $course)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $course->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words($course->title, 8, '...') }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($course->website)
                                                <span class="badge bg-primary-subtle text-primary">Website</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Interno</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($course->available)
                                                <span class="badge bg-success-subtle text-success">Publicado</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $course->updated_at->format('d/m/Y') }}</span>
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
                                                           href="{{ route('courses.view', $course->slack) }}"
                                                           target="_blank">
                                                            Ver en sitio
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.courses.navegation', $course->slack) }}">
                                                            Navegación
                                                        </a>
                                                    </li>
                                                    @can('courses.update')
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.courses.edit', $course->slack) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    @endcan
                                                    @can('courses.delete')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.courses.destroy', $course->slack) }}"
                                                           data-title="Eliminar: {{ $course->title }}">
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
                        <i class="fas fa-book-open fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if($searchKey || ($available ?? '') !== '' || ($website ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay cursos
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($searchKey || ($available ?? '') !== '' || ($website ?? '') !== '')
                                No hay cursos que coincidan con los filtros aplicados.
                            @else
                                Crea el primer curso de la plataforma.
                            @endif
                        </p>
                        @if($searchKey || ($available ?? '') !== '' || ($website ?? '') !== '')
                            <a href="{{ route('manager.courses') }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            <a href="{{ route('manager.courses.create') }}" class="btn btn-primary">
                                Nuevo curso
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
