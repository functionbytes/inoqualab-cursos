@extends('layouts.managers')

@section('content')
    <div class="container-fluid">

        <div class="row">
            <!-- Customers -->
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body pb-0 mb-xxl-2 pb-1">
                        <p class="mb-1 fs-5">Cursos</p>
                        <h4 class="fw-semibold fs-7">{{ $courses }}</h4>

                    </div>
                    <div class="customers" id="customers"></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body pb-0 mb-xxl-2 pb-1">
                        <p class="mb-1 fs-5">Empresas</p>
                        <h4 class="fw-semibold fs-7">{{ $enterprises }}</h4>

                    </div>
                    <div class="customers2" id="customers2"></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body pb-0 mb-xxl-2 pb-1">
                        <p class="mb-1 fs-5">Blogs</p>
                        <h4 class="fw-semibold fs-7">{{ $blogs }}</h4>

                    </div>
                    <div class="customers3" id="customers3"></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body pb-0 mb-xxl-2 pb-1">
                        <p class="mb-1 fs-5">Clientes</p>
                        <h4 class="fw-semibold fs-7">{{ $users }}</h4>

                    </div>
                    <div class="customers4" id="customers4"></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body pb-0 mb-xxl-2 pb-1">
                        <p class="mb-1 fs-5">Administradores</p>
                        <h4 class="fw-semibold fs-7">{{ $useradmins }}</h4>

                    </div>
                    <div class="customers5" id="customers5"></div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body pb-0 mb-xxl-2 pb-1">
                        <p class="mb-1 fs-5">Empresas</p>
                        <h4 class="fw-semibold fs-7">{{ $enterprises }}</h4>

                    </div>
                    <div class="customers6" id="customers6"></div>
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
                                <p class="card-subtitle mb-0">Detalle de facturación ordenes </p>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-lg-8 col-md-8">
                                <div id="charts"></div>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="d-flex align-items-center mb-4 pb-1">
                                    <div>
                                        <h4 class="mb-0 fs-7 fw-semibold">${{ number_format($analyticsEanings->sum('total')) }}</h4>
                                        <p class="fs-3 mb-0">Total ganancias</p>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex align-items-baseline mb-4">
                                        <span class="round-8 bg-primary rounded-circle me-6"></span>
                                        <div>
                                            <p class="fs-3 mb-1">Ordenes acuerdo</p>
                                            <h6 class="fs-5 fw-semibold mb-0">{{ $analyticsEaning->where('method_id', 1)->count() }}</h6>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-baseline mb-4 pb-1">
                                        <span class="round-8 bg-secondary rounded-circle me-6"></span>
                                        <div>
                                            <p class="fs-3 mb-1">Ordenes online</p>
                                            <h6 class="fs-5 fw-semibold mb-0">{{ $analyticsEaning->where('method_id', 2)->count() }}</h6>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('manager.orders') }}" class="btn btn-primary w-100">Visualizar ordenes</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 d-flex align-items-strech">
                <div class="card w-100">
                    <div class="card-body">
                        <div>
                            <h5 class="card-title fw-semibold mb-1">Ordenes</h5>
                            <p class="card-subtitle mb-0">Detalle ordenes por mes</p>
                            <div id="orders" class="mb-7 pb-8"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--  Row 3 -->
            <div class="row">
                <!-- Weekly Stats -->
                <div class="col-lg-12 d-flex align-items-strech">
                    <div class="card w-100">
                        <div class="card-body">
                            <div class="d-sm-flex d-block align-items-center justify-content-between mb-7">
                                <div class="mb-3 mb-sm-0">
                                    <h5 class="card-title fw-semibold">Solicitudes de contacto</h5>
                                    <p class="card-subtitle mb-0">Detalle de las ultimas solicitudes de soporte</p>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle text-nowrap mb-0">
                                    <thead>
                                    <tr class="text-muted fw-semibold">
                                        <th scope="col" class="ps-0">Cliente</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody class="border-top">
                                    @foreach($contacts as $contact)
                                        <tr>
                                            <td class="ps-0">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="fw-semibold mb-1">{{ Str::words( Str::upper(Str::lower($contact->firstname . ' ' . $contact->lastname)), 12, '...')  }}</h6>
                                                        <p class="fs-2 mb-0 text-muted">{{{ $contact->slack }}}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                            <span class="badge {{ $contact->reviewed == 1 ? 'bg-light-primary' : 'bg-light-secondary' }} rounded-3 py-2 text-primary fw-semibold fs-2 d-inline-flex align-items-center gap-1">
                                                 {{ $contact->reviewed == 1 ? 'Gestionado' : 'Pendiente' }}
                                              </span>
                                            </td>
                                            <td class="">
                                                <div class="dropdown">
                                                    <a class="text-decoration-none" href="{{ route('manager.contacts.edit', $contact->slack) }}" >
                                                        <i class="fas fa-ellipsis fs-4"></i>
                                                    </a>
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


                    // =====================================
                    // Customers
                    // =====================================
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
                    new ApexCharts(document.querySelector('.customers'), customers).render();
                    new ApexCharts(document.querySelector('.customers1'), customers).render();
                    new ApexCharts(document.querySelector('.customers2'), customers).render();
                    new ApexCharts(document.querySelector('.customers3'), customers).render();
                    new ApexCharts(document.querySelector('.customers4'), customers).render();
                    new ApexCharts(document.querySelector('.customers5'), customers).render();
                    new ApexCharts(document.querySelector('.customers6'), customers).render();
                    // =====================================
                    // Profit
                    // =====================================
                    var chart = {
                        series: [
                            {
                                name: "Ganancias este mes",
                                data: <?php echo json_encode($numberEanings); ?>,
                            },
                            {
                                name: "Gasto este mes",
                                data: <?php echo json_encode($numberEanings); ?>,
                            },
                        ],
                        chart: {
                            toolbar: {
                                show: false,
                            },
                            type: "bar",
                            fontFamily: "Plus Jakarta Sans', sans-serif",
                            foreColor: "#008bce",
                            height: 320,
                            stacked: true,
                        },
                        colors: ["#000", "#008bce"],
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                barHeight: "60%",
                                columnWidth: "20%",
                                borderRadius: [6],
                                borderRadiusApplication: 'end',
                                borderRadiusWhenStacked: 'all'
                            },
                        },
                        dataLabels: {
                            enabled: false,
                        },
                        legend: {
                            show: false,
                        },
                        grid: {
                            borderColor: "rgba(0,0,0,0.1)",
                            strokeDashArray: 3,
                            xaxis: {
                                lines: {
                                    show: false,
                                },
                            },
                        },
                        yaxis: {
                            min: -5,
                            max: 5,
                            title: {
                                // text: 'Age',
                            },
                        },
                        xaxis: {
                            axisBorder: {
                                show: false,
                            },
                            categories: <?php echo json_encode($monthEanings); ?>,

                        },
                        yaxis: {
                            tickAmount: 4,
                        },
                        tooltip: {
                            theme: "dark",
                        },
                    };

                    var chart = new ApexCharts(document.querySelector("#charts"), chart);
                    chart.render();


                    var order = {
                        series: [
                            {
                                name: "Ordenes",
                                data: <?php echo json_encode($viewOrders); ?>,
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

                            categories: [["Enero"], ["Febrero"], ["Marzo"], ["Abril"], ["Mayo"], ["Junio"], ["Julio"], ["Agosto"], ["Seoptiembre"], ["Octubre"], ["Noviembre"], ["Diciembre"]],
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

                    var orders = new ApexCharts(document.querySelector("#orders"), order);
                    orders.render();
                });

            </script>


    @endpush