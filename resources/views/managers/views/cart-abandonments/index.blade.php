@extends('layouts.managers')

@section('title', 'Carritos incompletos')

@section('content')

    <div class="widget-content searchable-container list" id="cart-abandonments-index"
         data-config='@json([
            "routes" => [
                "bulkAction" => route("manager.cart-abandonments.bulk-action"),
            ],
         ])'>

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div>
                    <h5 class="mb-1 fw-bold">Carritos incompletos</h5>
                    <p class="small mb-0 text-muted">
                        Correos capturados en el checkout (autenticados o invitados) que todavía no generaron una orden.
                        Se recuerdan automáticamente por correo una hora después.
                    </p>
                </div>
            </div>

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
                            'clear_url' => route('manager.cart-abandonments.index', array_filter(['search' => $search ?? ''])),
                        ];
                    }
                @endphp
                <form method="GET" action="{{ route('manager.cart-abandonments.index') }}">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por correo..."
                                       value="{{ $search ?? '' }}">
                            </div>
                        </div>

                        <select name="status" id="status-filter" class="form-select flex-shrink-0 w-auto">
                            <option value="">Todos los estados</option>
                            <option value="pending" @selected(($status ?? '') === 'pending')>Sin recordar</option>
                            <option value="reminded" @selected(($status ?? '') === 'reminded')>Recordados</option>
                            <option value="converted" @selected(($status ?? '') === 'converted')>Convertidos</option>
                        </select>

                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    @include('managers.includes.filter-chips', ['chips' => $filterChips])
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
                                            <span class="badge bg-warning-subtle text-warning">Recordado</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Sin recordar</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <p class="text-muted mb-0">{{ $abandonment->created_at->format('d/m/Y H:i') }}</p>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('cart.restore', $abandonment->slack) }}" target="_blank"
                                           class="text-muted" title="Ver el carrito como lo vería el cliente">
                                            <i class="fas fa-arrow-up-right-from-square"></i>
                                        </a>
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
                    <i class="fas fa-cart-shopping fa-3x mb-3 text-muted opacity-50"></i>
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

            @if($abandonments->hasPages())
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Mostrando {{ $abandonments->firstItem() }}–{{ $abandonments->lastItem() }} de {{ $abandonments->total() }} registros
                        </div>
                        {{ $abandonments->appends(request()->input())->links() }}
                    </div>
                </div>
            @endif

        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'registro(s)',
        'bulkActions' => [
            ['value' => 'delete', 'label' => 'Eliminar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/cart-abandonments/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('managers/js/views/cart-abandonments/index.js') }}"></script>
@endpush
