
@extends('layouts.managers')

@section('content')

    <div class="container-fluid" id="accounting-dashboard" data-config='@php $__jsonInline1 = [
        "monthValues" => $monthValues,
        "monthNames" => $monthNames,
        "yearValues" => $yearValues,
        "yearNames" => $yearNames,
    ]; @endphp@json($__jsonInline1)'>
        <div class="row">
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body pb-0 mb-xxl-2 pb-1">
                        <p class="mb-1 fs-5">Total órdenes</p>
                        <h4 class="fw-semibold fs-7">{{ $totalOrders }}</h4>
                    </div>
                    <div id="customers4"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body pb-0 mb-xxl-2 pb-1">
                        <p class="mb-1 fs-5"> Total facturas</p>
                        <h4 class="fw-semibold fs-7">{{ $totalInvoices }}</h4>
                    </div>
                    <div id="customers3"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body pb-0 mb-xxl-2 pb-1">
                        <p class="mb-1 fs-5">Total clientes </p>
                        <h4 class="fw-semibold fs-7">{{ $customers }}</h4>
                    </div>
                    <div id="customers2"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body pb-0 mb-xxl-2 pb-1">
                        <p class="mb-1 fs-5">Total empresas </p>
                        <h4 class="fw-semibold fs-7">{{ $enterprises }}</h4>
                    </div>
                    <div id="customers1"></div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-12 d-flex align-items-strech">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
                            <div class="mb-3 mb-sm-0">
                                <h5 class="card-title fw-semibold">Detalle facturación</h5>
                                <p class="card-subtitle mb-0">Detalle de facturación de ordenes </p>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-lg-8 col-md-8">
                                <div id="invoices"></div>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="d-flex align-items-center mb-4 pb-1">
                                    <div>
                                        <h4 class="mb-0 fs-7 fw-semibold">${{ number_format($yearInvoices->sum('total_invoices_amount')) }}</h4>
                                        <p class="fs-3 mb-0">Total ganancias</p>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex align-items-baseline mb-4">
                                        <span class="round-8 bg-primary rounded-circle me-6"></span>
                                        <div>
                                            <p class="fs-3 mb-1">Facturas efectivo</p>
                                            <h6 class="fs-5 fw-semibold mb-0">{{ $yearInvoices->where('method_id', 1)->count() }}</h6>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-baseline mb-4 pb-1">
                                        <span class="round-8 bg-secondary rounded-circle me-6"></span>
                                        <div>
                                            <p class="fs-3 mb-1">Facturas tarjeta</p>
                                            <h6 class="fs-5 fw-semibold mb-0">{{ $yearInvoices->where('method_id', 2)->count() }}</h6>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-baseline mb-4 pb-1">
                                        <span class="round-8 bg-secondary rounded-circle me-6"></span>
                                        <div>
                                            <p class="fs-3 mb-1">Facturas credito</p>
                                            <h6 class="fs-5 fw-semibold mb-0">{{ $yearInvoices->where('method_id', 3)->count() }}</h6>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('accounting.invoices') }}" class="btn btn-primary w-100 mb-4">Visualizar facturas</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(count($yearInvoices)>0)
            <div class="col-lg-12 d-flex align-items-strech">
                <div class="card w-100">
                    <div class="card-body">
                        <div>
                            <h5 class="card-title fw-semibold mb-1">Facturas</h5>
                            <p class="card-subtitle mb-0">Detalle de facturas por mes</p>
                            <div id="invoiceList" class="mb-7 pb-8"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif


            <div class="col-lg-12 d-flex align-items-strech">
                <!-- Weekly Stats -->

                    <div class="card w-100">
                        <div class="card-body">
                            <div class="d-sm-flex d-block align-items-center justify-content-between mb-7">
                                <div class="mb-3 mb-sm-0">
                                    <h5 class="card-title fw-semibold">Reporte facturacion ultimos 7 dias</h5>
                                    <p class="card-subtitle mb-0">Detalle de factura de los ultimos dias</p>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle text-nowrap mb-0">
                                    <thead>
                                    <tr class="text-muted fw-semibold">
                                        <th scope="col" class="ps-0">Cliente</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Metodo</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody class="border-top">
                                    @foreach($monthlyInvoices as $invoice)

                                        <tr>
                                            <td class="ps-0">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="fw-semibold mb-1">{{ Str::words( $invoice->reference)  }}</h6>
                                                        {{-- El distribuidor puede haberse borrado (soft delete) después de emitir la factura --}}
                                                        <p class="fs-2 mb-0 text-muted">{{ $invoice->distributor->title ?? 'N/D' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $invoice->condition->badge_class }} rounded-3 py-2 fw-semibold fs-2 d-inline-flex align-items-center gap-1">
                                                    {{ $invoice->condition->title }}
                                                </span>
                                                            </td>
                                                            <td>
                                                <span class="badge  bg-secondary-subtle text-secondary rounded-3 py-2 fw-semibold fs-2 d-inline-flex align-items-center gap-1">
                                                     {{ $invoice->method->title }}
                                                </span>
                                            </td>
                                            <td class="text-left">
                                                <div class="dropdown dropstart">
                                                    <a href="#" class="text-muted" id="dropdownMenuButton-{{ $loop->index }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-vertical fs-5"></i>
                                                    </a>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $loop->index }}">

                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('accounting.invoices.edit',$invoice->slack) }}">Editar</a>
                                                        </li>


                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('accounting.invoices.view',$invoice->slack) }}">General</a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('accounting.invoices.details',$invoice->slack) }}">Detallado</a>
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
                    </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ url('managers/libs/owl.carousel/dist/owl.carousel.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/libs/apexcharts/dist/apexcharts.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('accountings/js/views/dashboard/index.js') }}"></script>
@endpush


