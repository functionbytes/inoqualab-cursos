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
                <form method="GET" action="{{ route('manager.coupons') }}" id="searchForm">

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
                        'searchPlaceholder' => 'Buscar por código...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($coupons->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="coupons-col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Título</th>
                                    <th>Código</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Fecha inicio</th>
                                    <th class="text-center">Fecha finalización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($coupons as $coupon)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $coupon->id }}">
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::words($coupon->title, 12, '...') }}</div>
                                        </td>
                                        <td>
                                            <span class="font-monospace">{{ Str::upper($coupon->code) }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($coupon->available == 1)
                                                <span class="badge bg-success-subtle text-success">Público</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Oculto</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $coupon->start_date ?? '—' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $coupon->end_date ?? '—' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @can('coupons.update')
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.coupons.edit', $coupon->slack) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    @endcan
                                                    @can('coupons.delete')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item btn-delete" href="#"
                                                           data-url="{{ route('manager.coupons.destroy', $coupon->slack) }}"
                                                           data-title="Eliminar: {{ $coupon->title }}">
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
                        <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-ticket', 48) !!}</div>
                        <h5 class="fw-bold mb-2">
                            @if($searchKey || ($available ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay cupones
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if($searchKey || ($available ?? '') !== '')
                                No hay cupones que coincidan con los filtros aplicados.
                            @else
                                Crea el primer cupón de descuento de la plataforma.
                            @endif
                        </p>
                        @if($searchKey || ($available ?? '') !== '')
                            <a href="{{ route('manager.coupons') }}" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        @else
                            @can('coupons.create')
                            <a href="{{ route('manager.coupons.create') }}" class="btn btn-primary">
                                Nuevo cupón
                            </a>
                            @endcan
                        @endif
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $coupons,
                'itemLabel' => 'cupones',
            ])

        </div>
