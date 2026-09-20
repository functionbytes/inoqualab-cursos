@extends('layouts.managers')

@section('title', 'Ordenes')

@section('content')


    <div class="widget-content searchable-container list" id="orders-index"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Ordenes</h5>
                        <p class="mb-0 text-muted">Gestiona y consulta las órdenes registradas</p>
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('manager.orders.resumen') }}" class="btn btn-outline-secondary">
                            Resumen
                        </a>
                        <a href="{{ route('manager.orders.report') }}" class="btn btn-primary">
                            Reporte
                        </a>
                    </div>
                </div>
            </div>

            {{-- Search + Filtros --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ Request::url() }}" id="searchForm">

                    <input type="hidden" name="condition" id="filterCondition" value="{{ $condition ?? '' }}">
                    <input type="hidden" name="type"      id="filterType"      value="{{ $type ?? '' }}">
                    <input type="hidden" name="methods"   id="filterMethods"   value="{{ $method ?? '' }}">

                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por código de orden..."
                                       value="{{ $searchKey ?? '' }}">
                            </div>
                        </div>

                        @php
                            $activeFilters = (int)(($condition ?? '') !== '') + (int)(($type ?? '') !== '') + (int)(($method ?? '') !== '');
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
                                            <span class="badge bg-light-{{ $order->condition->slug }} text-primary rounded-3 py-2 fw-semibold">
                                                {{ $order->condition->title }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light-{{ $order->method->slug }} text-primary rounded-3 py-2 fw-semibold">
                                                {{ $order->method->title }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light-{{ $order->type->slug }} text-primary rounded-3 py-2 fw-semibold">
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
                        <label class="form-label fw-semibold">Estado pago</label>
                        <select id="modalCondition" class="form-select">
                            <option value="">Todos</option>
                            @foreach($conditions as $item)
                                <option value="{{ $item->id }}" {{ ($condition ?? '') == $item->id ? 'selected' : '' }}>
                                    {{ $item->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipo pago</label>
                        <select id="modalType" class="form-select">
                            <option value="">Todos</option>
                            @foreach($types as $item)
                                <option value="{{ $item->id }}" {{ ($type ?? '') == $item->id ? 'selected' : '' }}>
                                    {{ $item->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Metodo pago</label>
                        <select id="modalMethods" class="form-select">
                            <option value="">Todos</option>
                            @foreach($methods as $item)
                                <option value="{{ $item->id }}" {{ ($method ?? '') == $item->id ? 'selected' : '' }}>
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
                    <a href="{{ Request::url() }}" class="btn btn-secondary w-100">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.delete')

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/orders/orders/index.js') }}"></script>
@endpush
