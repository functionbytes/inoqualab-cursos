<div class="card">

            {{-- Header --}}
            

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                                <p class="text-muted">Capturados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Sin recordar</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['pending']) }}</h4>
                                <p class="text-muted">Esperando 1h</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Recordados</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['reminded']) }}</h4>
                                <p class="text-muted">Correo enviado, sin comprar aún</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Convertidos</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($stats['converted']) }}</h4>
                                <p class="text-muted">Terminaron generando la orden</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Tasa de conversión</h6>
                                <h4 class="mb-1 fw-bold">{{ $stats['conversion_rate'] }}%</h4>
                                <p class="text-muted">Del total capturado</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="card-body border-bottom">
                @php
                    $statusLabels = ['pending' => 'Sin recordar', 'reminded' => 'Recordado', 'converted' => 'Convertido'];
                    $filterChips = [];
                    if (($status ?? '') !== '') {
                        $filterChips[] = [
                            'label'     => 'Estado: ' . ($statusLabels[$status] ?? $status),
                            'clear_url' => url()->current() . '?' . http_build_query(request()->except('status')),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('manager.cart-abandonments.index') }}" id="searchForm">

                    <input type="hidden" name="status" id="filterStatus" value="{{ $status ?? '' }}">

                    @php ob_start(); @endphp
                    <div class="filter-popover-field">
                        <div class="filter-popover-label">Estado</div>
                        <div class="filter-popover-options">
                            <label class="filter-popover-option">
                                <input type="radio" data-filter-name="popover_status" value="" {{ ($status ?? '') === '' ? 'checked' : '' }}>
                                <span class="filter-popover-dot"></span>
                                <span>Todos</span>
                            </label>
                            @foreach($statusLabels as $value => $label)
                                <label class="filter-popover-option">
                                    <input type="radio" data-filter-name="popover_status" value="{{ $value }}" {{ ($status ?? '') === $value ? 'checked' : '' }}>
                                    <span class="filter-popover-dot"></span>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @php $popoverBody = trim(ob_get_clean()); @endphp

                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $search ?? '',
                        'searchPlaceholder' => 'Buscar por correo...',
                        'popoverBody' => $popoverBody,
                        'filterChips' => $filterChips,
                    ])
                </form>
            </div>

            {{-- Tabla --}}
            @if($abandonments->count() > 0)
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="cart-abandonments-col-checkbox">
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                </th>
                                <th>Correo</th>
                                <th>Carrito</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Capturado el</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($abandonments as $abandonment)
                                @php
                                    $items = $abandonment->items ?? [];
                                    $firstTitle = $items[0]['title'] ?? '—';
                                    $extraCount = count($items) - 1;
                                    $abandonmentStatus = $abandonment->converted_at ? 'converted' : ($abandonment->reminded_at ? 'reminded' : 'pending');
                                    $abandonmentItemsJson = collect($items)->map(fn ($line) => [
                                        'title' => $line['title'] ?? '',
                                        'type' => $line['type'] ?? '',
                                        'qty' => $line['qty'] ?? 1,
                                        'amount' => $line['amount'] ?? 0,
                                    ])->values()->toJson();
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input bulk-checkbox"
                                               value="{{ $abandonment->id }}">
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $abandonment->email }}</span>
                                        @if($abandonment->user)
                                            <br><small class="text-muted">{{ $abandonment->user->firstname }} {{ $abandonment->user->lastname }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ \Illuminate\Support\Str::limit($firstTitle, 45) }}
                                        @if($extraCount > 0)
                                            <span class="badge bg-light text-dark border">+{{ $extraCount }} más</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-semibold">$ {{ number_format((float) $abandonment->total, 0, ',', '.') }} COP</span>
                                    </td>
                                    <td class="text-center">
                                        @if($abandonment->converted_at)
                                            <span class="badge bg-success-subtle text-success">Convertido</span>
                                        @elseif($abandonment->reminded_at)
                                            <span class="badge bg-primary-subtle text-primary">Recordado</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Sin recordar</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <p class="text-muted mb-0">{{ $abandonment->created_at->format('d/m/Y H:i') }}</p>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <a href="#" class="text-muted" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                                <i class="fas fa-ellipsis-vertical"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button type="button" class="dropdown-item btn-view-abandonment"
                                                            data-email="{{ $abandonment->email }}"
                                                            data-name="{{ $abandonment->user ? trim($abandonment->user->firstname.' '.$abandonment->user->lastname) : '' }}"
                                                            data-cellphone="{{ $abandonment->user->cellphone ?? '' }}"
                                                            data-identification="{{ $abandonment->user->identification ?? '' }}"
                                                            data-total="{{ number_format((float) $abandonment->total, 0, ',', '.') }}"
                                                            data-status="{{ $abandonmentStatus }}"
                                                            data-created="{{ $abandonment->created_at->format('d/m/Y H:i') }}"
                                                            data-reminded="{{ $abandonment->reminded_at?->format('d/m/Y H:i') }}"
                                                            data-converted="{{ $abandonment->converted_at?->format('d/m/Y H:i') }}"
                                                            data-cart-url="{{ route('cart.restore', $abandonment->slack) }}"
                                                            data-remind-url="{{ route('manager.cart-abandonments.remind', $abandonment->id) }}"
                                                            data-items="{{ $abandonmentItemsJson }}">
                                                        Ver detalle
                                                    </button>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('cart.restore', $abandonment->slack) }}" target="_blank">
                                                        Ver carrito como cliente
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
            </div>
            @else
            <div class="card-body">
                <div class="text-center py-5">
                    <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-cart', 48) !!}</div>
                    <h5 class="fw-bold mb-2">No hay carritos incompletos</h5>
                    <p class="text-muted mb-0">
                        @if($search || ($status ?? '') !== '')
                            No se encontraron resultados con los filtros aplicados.
                        @else
                            Aparecerán aquí cuando alguien deje su correo en el checkout sin completar la compra.
                        @endif
                    </p>
                </div>
            </div>
            @endif

            @include('managers.includes.pagination-footer', [
                'paginator' => $abandonments,
                'itemLabel' => 'registros',
            ])

        </div>
