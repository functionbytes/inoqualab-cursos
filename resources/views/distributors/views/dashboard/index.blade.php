@extends('layouts.managers')

@section('page_header')
    @include('distributors.includes.card', [
        'title' => 'Panel de distribuidor',
        'description' => 'Gestiona tus empresas, matrículas, órdenes y facturas desde un solo lugar',
    ])
@endsection

@section('content')

    <div class="widget-content">

        {{-- Métricas --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dashboard-metric-icon rounded-4 d-flex align-items-center justify-content-center">
                            {!! \App\Html\IconHelper::render('nav-enterprises', 34) !!}
                        </div>
                        <div>
                            <div class="text-muted dashboard-metric-label">Empresas</div>
                            <div class="dashboard-metric-value">{{ number_format($enterprisesCount) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dashboard-metric-icon rounded-4 d-flex align-items-center justify-content-center">
                            {!! \App\Html\IconHelper::render('nav-people', 34) !!}
                        </div>
                        <div>
                            <div class="text-muted dashboard-metric-label">Matriculados</div>
                            <div class="dashboard-metric-value">{{ number_format($usersCount) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dashboard-metric-icon rounded-4 d-flex align-items-center justify-content-center">
                            {!! \App\Html\IconHelper::render('user-orders', 34) !!}
                        </div>
                        <div>
                            <div class="text-muted dashboard-metric-label">Órdenes</div>
                            <div class="dashboard-metric-value">{{ number_format($ordersCount) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dashboard-metric-icon rounded-4 d-flex align-items-center justify-content-center">
                            {!! \App\Html\IconHelper::render('nav-invoices', 34) !!}
                        </div>
                        <div>
                            <div class="text-muted dashboard-metric-label">Facturas</div>
                            <div class="dashboard-metric-value">{{ number_format($invoicesCount) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Órdenes recientes --}}
        <div class="card">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between gap-3">
                <div>
                    <h6 class="mb-0 fw-bold">Órdenes recientes</h6>
                    <p class="text-muted mb-0">Últimas órdenes registradas en tus empresas</p>
                </div>
                <a href="{{ route('distributor.orders') }}" class="btn dashboard-view-all">Ver todas las órdenes</a>
            </div>
            <div class="card-body p-0">
                @if($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Orden</th>
                                    <th>Empresa</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $order->slack }}</div>
                                            <div class="text-muted small">{{ Str::upper(trim(($order->user->firstname ?? 'Usuario eliminado').' '.($order->user->lastname ?? ''))) }}</div>
                                        </td>
                                        <td>{{ Str::upper($order->activity?->enterprise?->title ?? 'N/D') }}</td>
                                        <td class="text-muted">{{ $order->updated_at->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('distributor.orders.view', $order->slack) }}">Ver</a></li>
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
                        <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-inbox', 48) !!}</div>
                        <h5 class="fw-bold mb-2">No hay órdenes</h5>
                        <p class="text-muted mb-0">Aún no hay órdenes registradas en tus empresas.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('distributors/css/views/dashboard/index.css') }}">
@endpush
