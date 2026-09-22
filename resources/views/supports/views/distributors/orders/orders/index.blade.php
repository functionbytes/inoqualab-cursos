@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('support.distributors.orders.resumen', $distributor->slack) }}" class="btn btn-outline-secondary">
                            Resumen
                        </a>
                        <a href="{{ route('support.distributors.orders.reports', $distributor->slack) }}" class="btn btn-primary">
                            Reporte
                        </a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('supports.includes.card', [
        'title' => 'Ordenes de ' . $distributor->title,
        'description' => 'Gestiona y consulta las órdenes registradas para este distribuidor',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}">

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
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="condition" id="filterCondition" value="{{ $condition ?? '' }}">
                    <input type="hidden" name="type" id="filterType" value="{{ $type ?? '' }}">
                    <input type="hidden" name="methods" id="filterMethods" value="{{ $method ?? '' }}">

                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por código, cliente o identificación..."
                                       value="{{ $searchKey ?? '' }}">
                            </div>
                        </div>

                        @php
                            $activeFilters = (int) (($condition ?? '') !== '') + (int) (($type ?? '') !== '') + (int) (($method ?? '') !== '');
                        @endphp
                        <button type="button" class="btn btn-outline-secondary flex-shrink-0" title="Filtros"
                                data-bs-toggle="modal" data-bs-target="#filters-modal">
                            <i class="fas fa-sliders"></i>
                            @if($activeFilters > 0)
                                <span class="badge bg-primary ms-1">{{ $activeFilters }}</span>
                            @endif
                        </button>

                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
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
                                    <th>Numero</th>
                                    <th>Identificación</th>
                                    <th>Cliente</th>
                                    <th>Empresa</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Actualización</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $order->slack }}</span>
                                        </td>
                                        <td>{{ $order->reference }}</td>
                                        <td>{{ Str::upper($order->user->identification ?? 'N/D') }}</td>
                                        <td>{{ Str::upper(($order->user->firstname ?? 'N/D').' '.($order->user->lastname ?? '')) }}</td>
                                        <td>{{ Str::upper($order->activity->enterprise->title ?? 'N/D') }}</td>
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
                                                        <a class="dropdown-item" href="{{ route('support.distributors.orders.view', $order->slack) }}">
                                                            Visualizar
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
                                Las órdenes de este distribuidor aparecerán aquí cuando se registren.
                            @endif
                        </p>
                        @if(($searchKey ?? '') !== '' || $activeFilters > 0)
                            <a href="{{ route('support.distributors.orders', $distributor->slack) }}" class="btn btn-outline-secondary">
                                Ver todas
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if($orders->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $orders->firstItem() }}–{{ $orders->lastItem() }} de {{ $orders->total() }} ordenes
                    </span>
                    {{ $orders->appends(request()->input())->links() }}
                </div>
            @endif

        </div>
    </div>

    {{-- Filters modal --}}
    <div class="modal fade" id="filters-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filtros avanzados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estado</label>
                        <select id="modalCondition" class="form-select">
                            <option value="">Todos</option>
                            @foreach($conditions as $item)
                                <option value="{{ $item->id }}" {{ (string) ($condition ?? '') === (string) $item->id ? 'selected' : '' }}>
                                    {{ $item->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipo</label>
                        <select id="modalType" class="form-select">
                            <option value="">Todos</option>
                            @foreach($types as $item)
                                <option value="{{ $item->id }}" {{ (string) ($type ?? '') === (string) $item->id ? 'selected' : '' }}>
                                    {{ $item->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Metodo de pago</label>
                        <select id="modalMethods" class="form-select">
                            <option value="">Todos</option>
                            @foreach($methods as $item)
                                <option value="{{ $item->id }}" {{ (string) ($method ?? '') === (string) $item->id ? 'selected' : '' }}>
                                    {{ $item->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="applyFiltersBtn" class="btn btn-primary w-100 mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ route('support.distributors.orders', $distributor->slack) }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('supports/js/views/distributors/orders/orders/index.js') }}"></script>
@endpush
