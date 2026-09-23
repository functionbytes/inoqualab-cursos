@extends('layouts.managers')

@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100 content-dashboard">
            <div class="position-relative">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="mb-7 mt-6">
                            <h2 class="fw-semibold mb-1 text-uppercase">Bienvenido!</h2>
                            <p class="text-black">Como distribuidor puedes gestionar tus empresas, matricular estudiantes y hacer seguimiento a sus inscripciones, pedidos y facturas desde un solo lugar.</p>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="welcome-bg-img mb-n7 text-end">
                            <img src="/customers/images/dashboard/dashboard.svg" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-1">

    <div class="col-6 col-lg-3">
        <div class="card w-100 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="dist-stat-icon dist-stat-icon--enterprises">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <h4 class="fw-semibold mb-0">{{ number_format($enterprisesCount) }}</h4>
                    <p class="text-muted mb-0">Empresas</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card w-100 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="dist-stat-icon dist-stat-icon--users">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h4 class="fw-semibold mb-0">{{ number_format($usersCount) }}</h4>
                    <p class="text-muted mb-0">Usuarios matriculados</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card w-100 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="dist-stat-icon dist-stat-icon--orders">
                    <i class="fas fa-cart-shopping"></i>
                </div>
                <div>
                    <h4 class="fw-semibold mb-0">{{ number_format($ordersCount) }}</h4>
                    <p class="text-muted mb-0">Ordenes</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card w-100 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="dist-stat-icon dist-stat-icon--invoices">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div>
                    <h4 class="fw-semibold mb-0">{{ number_format($invoicesCount) }}</h4>
                    <p class="text-muted mb-0">Facturas</p>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-header border-bottom d-sm-flex d-block align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1 fw-bold">Ordenes recientes</h6>
                    <p class="text-muted small mb-0">Ultimas ordenes registradas en tus empresas</p>
                </div>
                <a href="{{ route('distributor.orders') }}" class="btn btn-sm btn-outline-brand mt-2 mt-sm-0">Ver todas</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle text-nowrap mb-0">
                        <thead>
                            <tr class="text-muted fw-semibold">
                                <th scope="col">Orden</th>
                                <th scope="col">Cliente</th>
                                <th scope="col">Empresa</th>
                                <th scope="col">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="border-top">
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td>{{ $order->slack }}</td>
                                    <td>{{ Str::upper(trim(($order->user->firstname ?? 'Usuario eliminado').' '.($order->user->lastname ?? ''))) }}</td>
                                    <td>{{ Str::upper($order->activity?->enterprise?->title ?? 'N/D') }}</td>
                                    <td><span class="text-muted">{{ $order->updated_at->format('d/m/Y') }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Aun no hay ordenes registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('distributors/css/views/dashboard/index.css') }}">
@endpush
