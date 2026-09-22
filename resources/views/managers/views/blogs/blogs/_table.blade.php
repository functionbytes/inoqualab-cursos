<div class="card">

            {{-- Header --}}
            

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if (($available ?? '') !== '') {
                        $__popoverLabels_Available = ['1' => 'Público', '0' => 'Oculto'];
                        $filterChips[] = [
                            'label' => 'Estado: ' . ($__popoverLabels_Available[$available ?? ''] ?? ($available ?? '')),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('available')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

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
                        <span>Público</span>
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
                        'searchPlaceholder' => 'Buscar por título...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($blogs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="blogs-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Título</th>
                                    <th>Categoría</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($blogs as $blog)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $blog->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words($blog->title, 8, '...') }}</div>
                                        </td>
                                        <td>
                                            @if($blog->categorie)
                                                <span class="badge bg-primary-subtle text-primary">{{ $blog->categorie->title }}</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Sin categoría</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($blog->available)
                                                <span class="badge bg-success-subtle text-success">Público</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $blog->updated_at->format('d/m/Y') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @can('blogs.update')
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.blogs.edit', $blog->slack) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    @endcan
                                                    @can('blogs.delete')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.blogs.destroy', $blog->slack) }}"
                                                           data-title="Eliminar: {{ $blog->title }}">
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
                        <i class="fas fa-newspaper fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay noticias
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No hay noticias que coincidan con los filtros aplicados.
                            @else
                                Crea la primera noticia del blog.
                            @endif
                        </p>
                        @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @else
                            @can('blogs.create')
                            <a href="{{ route('manager.blogs.create') }}" class="btn btn-primary">
                                Nueva noticia
                            </a>
                            @endcan
                        @endif
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $blogs,
                'itemLabel' => 'noticias',
            ])

        </div>
