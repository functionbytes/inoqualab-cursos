<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted">Facturas emitidas</span>
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
            if (($method ?? '') !== '') {
                $__popoverLabels_Method = $methods->pluck('title', 'id');
                $filterChips[] = [
                    'label' => 'Método: ' . ($__popoverLabels_Method[$method ?? ''] ?? ($method ?? '')),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('methods')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('support.distributors.invoices', $distributor->slack) }}" id="searchForm">

            <input type="hidden" name="condition" id="filterCondition" value="{{ $condition ?? '' }}">
            <input type="hidden" name="methods" id="filterMethod" value="{{ $method ?? '' }}">

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
        <div class="filter-popover-field">
            <div class="filter-popover-label">Método de pago</div>
            <div class="filter-popover-options">
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Method" value="" {{ ($method ?? '') === '' ? 'checked' : '' }}>
                <span class="filter-popover-dot"></span>
                <span>Todos</span>
            </label>
            @foreach($methods as $item)
            <label class="filter-popover-option">
                <input type="radio" data-filter-name="popover_Method" value="{{ $item->id }}" {{ (string) ($method ?? '') === (string) $item->id ? 'checked' : '' }}>
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
                'searchPlaceholder' => 'Buscar por referencia, empresa, NIT o correo...',
                'popoverBody' => $popoverBody,
                'filterChips' => $filterChips,
            ])
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card-body">
        @if($invoices->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Factura</th>
                            <th>Empresa</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Metodo de pago</th>
                            <th class="text-center">Actualización</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $invoice->reference }}</div>
                                </td>
                                <td>{{ $invoice->distributor->title ?? 'N/D' }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $invoice->condition->badge_class }} rounded-3 py-2 fw-semibold">
                                        {{ $invoice->condition->title }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary rounded-3 py-2 fw-semibold">
                                        {{ $invoice->method->title }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="text-muted">{{ $invoice->updated_at->format('d/m/Y') }}</span>
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
                                                <a class="dropdown-item" href="{{ route('support.distributors.invoices.view', $invoice->slack) }}">
                                                    General
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('support.distributors.invoices.detail', $invoice->slack) }}">
                                                    Detallado
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-invoice', 48) !!}</div>
                <h5 class="fw-bold mb-2">
                    @if(($searchKey ?? '') || ($condition ?? '') || ($method ?? ''))
                        No se encontraron resultados
                    @else
                        No hay facturas
                    @endif
                </h5>
                <p class="text-muted mb-4">
                    @if(($searchKey ?? '') || ($condition ?? '') || ($method ?? ''))
                        No hay facturas que coincidan con los filtros aplicados.
                    @else
                        Este distribuidor todavía no tiene facturas emitidas.
                    @endif
                </p>
                @if(($searchKey ?? '') || ($condition ?? '') || ($method ?? ''))
                    <a href="{{ route('support.distributors.invoices', $distributor->slack) }}" class="btn btn-outline-secondary">
                        Ver todos
                    </a>
                @endif
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $invoices,
        'itemLabel' => 'facturas',
    ])

</div>
