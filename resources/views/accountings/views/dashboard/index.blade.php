
@extends('layouts.accountings')

@section('content')

    <div class="container-fluid">
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
                                                        <p class="fs-2 mb-0 text-muted">{{$invoice->distributor->title}}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light-{{$invoice->condition->slug }} rounded-3 py-2 text-primary fw-semibold fs-2 d-inline-flex align-items-center gap-1">
                                                    {{ $invoice->condition->title }}
                                                </span>
                                                            </td>
                                                            <td>
                                                <span class="badge  bg-light-{{ $invoice->method->slug }} rounded-3 py-2 text-primary fw-semibold fs-2 d-inline-flex align-items-center gap-1">
                                                     {{ $invoice->method->title }}
                                                </span>
                                            </td>
                                            <td class="text-left">
                                                <div class="dropdown dropstart">
                                                    <a href="#" class="text-muted" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis fs-5"></i>
                                                    </a>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">

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

    <script>
        $(document).ready(function() {


            var customers = {
                chart: {
                    id: "sparkline3",
                    type: "area",
                    fontFamily: "Plus Jakarta Sans', sans-serif",
                    foreColor: "#008bce",
                    height: 60,
                    sparkline: {
                        enabled: true,
                    },
                    group: "sparklines",
                },
                series: [
                    {
                        name: "Clientes",
                        color: "#000",
                        data: [30, 25, 35, 20, 30, 40],
                    },
                ],
                stroke: {
                    curve: "smooth",
                    width: 2,
                },
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 0,
                        inverseColors: false,
                        opacityFrom: 0.12,
                        opacityTo: 0,
                        stops: [20, 180],
                    },
                },
                markers: {
                    size: 0,
                },
                tooltip: {
                    theme: "dark",
                    fixed: {
                        enabled: true,
                        position: "right",
                    },
                    x: {
                        show: false,
                    },
                },
            };
            new ApexCharts(document.querySelector('#customers1'), customers).render();
            new ApexCharts(document.querySelector('#customers2'), customers).render();
            new ApexCharts(document.querySelector('#customers3'), customers).render();
            new ApexCharts(document.querySelector('#customers4'), customers).render();


            var invoice = {
                series: [
                    {
                        name: "Facturas",
                        data: @json($monthValues),
                    },
                ],
                chart: {
                    toolbar: {
                        show: false,
                    },
                    height: 260,
                    type: "bar",
                    fontFamily: "Plus Jakarta Sans', sans-serif",
                    foreColor: "#000",
                },
                colors: ["#000", "#008bce", "#000", "#4f8ac8", "#000", "#4f8ac8"],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: "45%",
                        distributed: true,
                        endingShape: "rounded",
                    },
                },

                dataLabels: {
                    enabled: false,
                },
                legend: {
                    show: false,
                },
                grid: {
                    yaxis: {
                        lines: {
                            show: false,
                        },
                    },
                    xaxis: {
                        lines: {
                            show: false,
                        },
                    },
                },
                xaxis: {
                    categories: @json($monthNames),
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                },
                yaxis: {
                    labels: {
                        show: false,
                    },
                },
                tooltip: {
                    theme: "dark",
                },
            };

            var invoices = new ApexCharts(document.querySelector("#invoices"), invoice);
            invoices.render();


            var order = {
                series: [
                    {
                        name: "Facturas",
                        data: @json($yearValues),
                    },
                ],

                chart: {
                    toolbar: {
                        show: false,
                    },
                    height: 260,
                    type: "bar",
                    fontFamily: "Plus Jakarta Sans', sans-serif",
                    foreColor: "#000",
                },
                colors: ["#000", "#008bce", "#000", "#4f8ac8", "#000", "#4f8ac8"],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: "45%",
                        distributed: true,
                        endingShape: "rounded",
                    },
                },

                dataLabels: {
                    enabled: false,
                },
                legend: {
                    show: false,
                },
                grid: {
                    yaxis: {
                        lines: {
                            show: false,
                        },
                    },
                    xaxis: {
                        lines: {
                            show: false,
                        },
                    },
                },
                xaxis: {

                    categories: @json($yearNames),
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                },
                yaxis: {
                    labels: {
                        show: false,
                    },
                },
                tooltip: {
                    theme: "dark",
                },
            };

            var orders = new ApexCharts(document.querySelector("#invoiceList"), order);
            orders.render();




        });
    </script>
@endpush


