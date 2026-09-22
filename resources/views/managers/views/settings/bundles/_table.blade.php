<div class="card">

            {{-- Header --}}
            

            {{-- Search + filtros --}}
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
                        'searchPlaceholder' => 'Buscar por título...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($bundles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="bundles-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Titulo</th>
                                    <th>Precio</th>
                                    <th class="text-center">Cursos</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Vence</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bundles as $bundle)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $bundle->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @php $thumb = $bundle->getFirstMedia('thumbnail'); @endphp
                                                @if($thumb)
                                                    <img src="{{ $thumb->getFullUrl() }}"
                                                         alt="{{ $bundle->title }}"
                                                         class="rounded bundles-thumb"
                                                         width="38" height="38">
                                                @endif
                                                <span class="fw-semibold">
                                                    {{ Str::words($bundle->title, 10, '...') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>${{ number_format($bundle->price, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            {{ $bundle->courses_count ?? $bundle->courses->count() }}
                                        </td>
                                        <td class="text-center">
                                            @if($bundle->available)
                                                <span class="badge bg-success-subtle text-success">Publico</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($bundle->expire_at)
                                                @php $expired = \Carbon\Carbon::parse($bundle->expire_at)->isPast(); @endphp
                                                <span class="{{ $expired ? 'text-danger fw-semibold' : 'text-muted' }}">
                                                    {{ \Carbon\Carbon::parse($bundle->expire_at)->format('d/m/Y') }}
                                                    @if($expired)
                                                        (vencido)
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
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
                                                           href="{{ route('bundles.view', [$bundle->slug ?? $bundle->slack]) }}"
                                                           target="_blank">
                                                            Ver en sitio
                                                        </a>
                                                    </li>
                                                    @can('bundles.update')
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.bundles.edit', $bundle->slack) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item btn-toggle-available" href="javascript:void(0)"
                                                           data-slack="{{ $bundle->slack }}"
                                                           data-available="{{ $bundle->available }}">
                                                            {{ $bundle->available ? 'Ocultar' : 'Publicar' }}
                                                        </a>
                                                    </li>
                                                    @endcan
                                                    @can('bundles.delete')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.bundles.destroy', $bundle->slack) }}"
                                                           data-title="Eliminar: {{ $bundle->title }}">
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
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay paquetes
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                                No hay paquetes que coincidan con los filtros aplicados.
                            @else
                                Crea el primer paquete de cursos de la plataforma.
                            @endif
                        </p>
                        @if(($searchKey ?? '') !== '' || ($available ?? '') !== '')
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            @can('bundles.create')
                            <a href="{{ route('manager.bundles.create') }}" class="btn btn-primary">
                                Nuevo paquete
                            </a>
                            @endcan
                        @endif
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $bundles,
                'itemLabel' => 'paquetes',
            ])

        </div>
