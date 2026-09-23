<div class="card">

            {{-- Header --}}
            

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $filterChips = [];
                    if (($condition ?? '') !== '') {
                        $filterChips[] = [
                            'label' => 'Estado: ' . (optional($conditions->firstWhere('id', $condition))->title ?? $condition),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('condition')),
                        ];
                    }
                    if (($method ?? '') !== '') {
                        $filterChips[] = [
                            'label' => 'Metodo de pago: ' . (optional($methods->firstWhere('id', $method))->title ?? $method),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('methods')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="condition" id="filterCondition" value="{{ $condition ?? '' }}">
                    <input type="hidden" name="methods"   id="filterMethods"   value="{{ $method ?? '' }}">

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
                                <input type="radio" data-filter-name="popover_Condition" value="{{ $item->id }}" {{ ($condition ?? '') == $item->id ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>{{ $item->title }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="filter-popover-field">
                    <div class="filter-popover-label">Metodo de pago</div>
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
                        'searchPlaceholder' => 'Buscar factura...',
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
                                    <th class="col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Factura</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Metodo de pago</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox"
                                                   value="{{ $invoice->id }}">
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ $invoice->reference }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $invoice->condition->badge_class }}">
                                                {{ $invoice->condition->title }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary">
                                                {{ $invoice->method->title }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ date('d/m/Y', strtotime($invoice->updated_at)) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @can('invoices.update')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.invoices.edit', $invoice->slack) }}">
                                                            Editar
                                                        </a>
                                                    </li>
                                                    @endcan
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.invoices.view', $invoice->slack) }}">
                                                            General
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('manager.invoices.details', $invoice->slack) }}">
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
                            @if(($searchKey ?? '') !== '' || ($condition ?? '') !== '' || ($method ?? '') !== '')
                                No se encontraron resultados
                            @else
                                No hay facturas
                            @endif
                        </h5>
                        <p class="text-muted mb-4">
                            @if(($searchKey ?? '') !== '' || ($condition ?? '') !== '' || ($method ?? '') !== '')
                                No hay facturas que coincidan con los filtros aplicados.
                            @else
                                Las facturas aparecerán aquí cuando se registren pagos.
                            @endif
                        </p>
                        @if(($searchKey ?? '') !== '' || ($condition ?? '') !== '' || ($method ?? '') !== '')
                            <a href="{{ Request::url() }}" class="btn btn-outline-secondary">
                                Ver todas
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
