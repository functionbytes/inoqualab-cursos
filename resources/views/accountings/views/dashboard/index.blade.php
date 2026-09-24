@extends('layouts.managers')

@section('page_header')
    @include('accountings.includes.card', [
        'title' => 'Panel de contabilidad',
        'description' => 'Resumen de facturación y órdenes',
    ])
@endsection

@section('content')

    <div class="widget-content" id="accounting-dashboard" data-config='@php $__jsonInline1 = [
        "monthValues" => $monthValues,
        "monthNames" => $monthNames,
        "yearValues" => $yearValues,
        "yearNames" => $yearNames,
    ]; @endphp@json($__jsonInline1)'>

        {{-- Métricas --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dashboard-metric-icon rounded-4 d-flex align-items-center justify-content-center">
                            {!! \App\Html\IconHelper::render('user-orders', 34) !!}
                        </div>
                        <div>
                            <div class="text-muted dashboard-metric-label">Órdenes</div>
                            <div class="dashboard-metric-value">{{ number_format($totalOrders) }}</div>
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
                            <div class="dashboard-metric-value">{{ number_format($totalInvoices) }}</div>
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
                            <div class="text-muted dashboard-metric-label">Clientes</div>
                            <div class="dashboard-metric-value">{{ number_format($customers) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dashboard-metric-icon rounded-4 d-flex align-items-center justify-content-center">
                            {!! \App\Html\IconHelper::render('nav-enterprises', 34) !!}
                        </div>
                        <div>
                            <div class="text-muted dashboard-metric-label">Empresas</div>
                            <div class="dashboard-metric-value">{{ number_format($enterprises) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detalle facturación --}}
        <div class="card mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">Detalle facturación</h6>
                <p class="text-muted mb-0">Facturas emitidas alrededor del mes actual</p>
            </div>
            <div class="card-body">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <div id="invoices"></div>
                    </div>
                    <div class="col-lg-4">
                        <div class="dashboard-total-value">$ {{ number_format($yearInvoices->sum('total_invoices_amount'), 0, ',', '.') }}</div>
                        <div class="text-muted mb-4">Total facturado este año</div>

                        <ul class="list-unstyled mb-4">
                            <li class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="d-flex align-items-center gap-2"><span class="dashboard-dot dashboard-dot-primary"></span>Efectivo</span>
                                <span class="fw-semibold">{{ $yearInvoices->where('method_id', 1)->count() }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="d-flex align-items-center gap-2"><span class="dashboard-dot dashboard-dot-dark"></span>Tarjeta</span>
                                <span class="fw-semibold">{{ $yearInvoices->where('method_id', 2)->count() }}</span>
                            </li>
                            <li class="d-flex align-items-center justify-content-between py-2">
                                <span class="d-flex align-items-center gap-2"><span class="dashboard-dot dashboard-dot-muted"></span>Crédito</span>
                                <span class="fw-semibold">{{ $yearInvoices->where('method_id', 3)->count() }}</span>
                            </li>
                        </ul>

                        <a href="{{ route('accounting.invoices') }}" class="btn btn-primary w-100">Ver facturas</a>
                    </div>
                </div>
            </div>
        </div>

        @if(count($yearInvoices) > 0)
            {{-- Facturas por mes --}}
            <div class="card mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">Facturas por mes</h6>
                    <p class="text-muted mb-0">Cantidad de facturas emitidas en {{ now()->year }}</p>
                </div>
                <div class="card-body">
                    <div id="invoiceList"></div>
                </div>
            </div>
        @endif

        {{-- Últimas facturas --}}
        <div class="card">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">Últimas facturas</h6>
                <p class="text-muted mb-0">Facturas más recientes emitidas este año</p>
            </div>
            <div class="card-body p-0">
                @if($latestInvoices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Factura</th>
                                    <th>Estado</th>
                                    <th>Método</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestInvoices as $invoice)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $invoice->reference }}</div>
                                            {{-- El distribuidor puede haberse borrado (soft delete) después de emitir la factura --}}
                                            <div class="text-muted small">{{ $invoice->distributor->title ?? 'N/D' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $invoice->condition->badge_class ?? 'bg-secondary-subtle text-secondary' }}">{{ $invoice->condition->title ?? 'N/D' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $invoice->method->title ?? 'N/D' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('accounting.invoices.edit', $invoice->slack) }}">Editar</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('accounting.invoices.view', $invoice->slack) }}">General</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('accounting.invoices.details', $invoice->slack) }}">Detallado</a></li>
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
                        <h5 class="fw-bold mb-2">No hay facturas</h5>
                        <p class="text-muted mb-0">Aún no se han emitido facturas este año.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('accountings/css/views/dashboard/index.css') }}">
@endpush

@push('scripts')
    <script src="{{ url('managers/libs/apexcharts/dist/apexcharts.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('accountings/js/views/dashboard/index.js') }}"></script>
@endpush
