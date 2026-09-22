<div class="card">

            {{-- Header --}}
            

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if (($condition ?? '') !== '') {
                        $filterChips[] = [
                            'label' => 'Estado pago: ' . (optional($conditions->firstWhere('id', $condition))->title ?? $condition),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('condition')),
                        ];
                    }
                    if (($type ?? '') !== '') {
                        $filterChips[] = [
                            'label' => 'Tipo pago: ' . (optional($types->firstWhere('id', $type))->title ?? $type),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('type')),
                        ];
                    }
                    if (($method ?? '') !== '') {
                        $filterChips[] = [
                            'label' => 'Metodo pago: ' . (optional($methods->firstWhere('id', $method))->title ?? $method),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('methods')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="condition" id="filterCondition" value="{{ $condition ?? '' }}">
                    <input type="hidden" name="type"      id="filterType"      value="{{ $type ?? '' }}">
                    <input type="hidden" name="methods"   id="filterMethods"   value="{{ $method ?? '' }}">

                    @php ob_start(); @endphp
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Estado pago</div>
                    <div class="filter-popover-options">
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_Condition" value="" {{ ($condition ?? '') === '' ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>Todos</span>
                        </label>
                        @foreach($conditions as $item)
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_Condition" value="{{ $item->id }}" {{ ($condition ?? '') == $item->id ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>{{ $item->title }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Tipo pago</div>
                    <div class="filter-popover-options">
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_Type" value="" {{ ($type ?? '') === '' ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>Todos</span>
                        </label>
                        @foreach($types as $item)
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_Type" value="{{ $item->id }}" {{ ($type ?? '') == $item->id ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>{{ $item->title }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Metodo pago</div>
                    <div class="filter-popover-options">
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_Methods" value="" {{ ($method ?? '') === '' ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>Todos</span>
                        </label>
                        @foreach($methods as $item)
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_Methods" value="{{ $item->id }}" {{ ($method ?? '') == $item->id ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>{{ $item->title }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                    @php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $searchKey ?? '',
                        'searchPlaceholder' => 'Buscar por código de orden...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Orden</th>
                                    <th>Cliente</th>
                                    <th>Metodo pago</th>
                                    <th>Estado pago</th>
                                    <th>Tipo pago</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $order->slack }}</span>
                                        </td>
                                        <td>
                                            {{ $order->user ? strtoupper($order->user->firstname.' '.$order->user->lastname) : 'USUARIO ELIMINADO' }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $order->condition->badge_class }} rounded-3 py-2 fw-semibold">
                                                {{ $order->condition->title }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary rounded-3 py-2 fw-semibold">
                                                {{ $order->method->title }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary rounded-3 py-2 fw-semibold">
                                                {{ $order->type->title }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($order->updated_at)) }}</span>
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
                                                        <a class="dropdown-item" href="{{ route('manager.orders.view', $order->slack) }}">
                                                            Visualizar
                                                        </a>
                                                    </li>
                                                    @can('orders.update')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.orders.edit', $order->slack) }}">
                                                            Editar
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
                        <i class="fas fa-receipt fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">
                            @if(($searchKey ?? '') !== '' || $activeFilters > 0)
                                No se encontraron resultados
                            @else
                                No hay ordenes
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') !== '' || $activeFilters > 0)
                                No hay órdenes que coincidan con los filtros aplicados.
                            @else
                                Las órdenes aparecerán aquí cuando se registren.
                            @endif
                        </p>
                        @if(($searchKey ?? '') !== '' || $activeFilters > 0)
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $orders,
                'itemLabel' => 'ordenes',
            ])

        </div>
