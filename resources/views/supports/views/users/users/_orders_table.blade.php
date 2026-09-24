<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted">Ordenes registradas</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Pagadas</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['paid']) }}</h4>
                        <span class="text-muted">Con pago confirmado</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Pendientes</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['pending']) }}</h4>
                        <span class="text-muted">Sin pago registrado</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Rechazadas</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['rejected']) }}</h4>
                        <span class="text-muted">No procesadas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search + Filtros --}}
    <div class="card-body border-bottom">
        @php
            $filterChips = [];
            if (($condition ?? '') !== '') {
                $__popoverLabels_Condition = $conditions->pluck('title', 'id');
                $filterChips[] = [
                    'label' => 'Estado: ' . ($__popoverLabels_Condition[$condition ?? ''] ?? ($condition ?? '')),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('condition')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('support.users.orders.index', $user->slack) }}" id="searchForm">

            <input type="hidden" name="condition" id="filterCondition" value="{{ $condition ?? '' }}">

            @php ob_start(); @endphp
        <div class="filter-popover-field">
            <div class="filter-popover-label">Estado</div>
            <div class="filter-popover-options">
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Condition" value="" {{ ($condition ?? '') === '' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Todos</span>
            </label>
            @foreach($conditions as $item)
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Condition" value="{{ $item->id }}" {{ (string) ($condition ?? '') === (string) $item->id ? 'checked' : '' }}>
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
                'searchPlaceholder' => 'Buscar por orden o numero...',
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
                            <th class="orders-col-checkbox"><input type="checkbox" class="form-check-input" id="select-all"></th>
                            <th>Orden</th>
                            <th>Numero</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                    @foreach ($orders as $order)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $order->id }}">
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $order->slack }}</span>
                            </td>
                            <td>{{ $order->reference }}</td>
                            <td class="text-center">
                                <span class="badge {{ $order->condition->badge_class }} rounded-3 py-2 fw-semibold">
                                    {{ $order->condition->title }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted">{{ $order->updated_at->format('d/m/Y') }}</span>
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
                                            <a class="dropdown-item" href="{{ route('support.users.orders.view', $order->slack) }}">Visualizar</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('support.users.orders.edit', $order->slack) }}">Editar</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('support.users.orders.print', $order->slack) }}" target="_blank">Imprimir</a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item confirm-delete" href="#"
                                               data-href="{{ route('support.users.orders.destroy', $order->slack) }}">Eliminar</a>
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-invoice', 48) !!}</div>
                <h5 class="fw-bold mb-2">
                    @if(($searchKey ?? '') !== '' || ($condition ?? '') !== '')
                        No se encontraron resultados
                    @else
                        No hay ordenes
                    @endif
                </h5>
                <p class="text-muted mb-4">
                    @if(($searchKey ?? '') !== '' || ($condition ?? '') !== '')
                        No hay órdenes que coincidan con los filtros aplicados.
                    @else
                        Este usuario todavia no tiene ordenes registradas.
                    @endif
                </p>
                @if(($searchKey ?? '') !== '' || ($condition ?? '') !== '')
                    <a href="{{ route('support.users.orders.index', $user->slack) }}" class="btn btn-outline-secondary">
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
