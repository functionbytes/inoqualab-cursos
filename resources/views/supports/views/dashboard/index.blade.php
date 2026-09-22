@extends('layouts.managers')


    @section('content')
        <div class="container-fluid" id="dashboard-content" data-config='@php $__jsonInline1 = [
            "numberEarnings" => $numberEanings,
            "monthEarnings" => $monthEanings,
            "viewOrders" => $viewOrders,
        ]; @endphp@json($__jsonInline1)'>

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
                            <h4 class="fw-semibold fs-7">{{ $usercustomers }}</h4>

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
                            <p class="mb-1 fs-5">Pedidos</p>
                            <h4 class="fw-semibold fs-7">{{ $orders }}</h4>

                        </div>
                        <div class="customers6" id="customers6"></div>
                    </div>
                </div>

            </div>

            <div class="row">
{{--                <div class="col-lg-12 d-flex align-items-strech">--}}
{{--                    <div class="card w-100">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">--}}
{{--                                <div class="mb-3 mb-sm-0">--}}
{{--                                    <h5 class="card-title fw-semibold">Detalle facturación</h5>--}}
{{--                                    <p class="card-subtitle mb-0">Detalle de facturación ordenes </p>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="row align-items-center">--}}
{{--                                <div class="col-lg-8 col-md-8">--}}
{{--                                    <div id="charts"></div>--}}
{{--                                </div>--}}
{{--                                <div class="col-lg-4 col-md-4">--}}
{{--                                    <div class="d-flex align-items-center mb-4 pb-1">--}}
{{--                                        <div>--}}
{{--                                            <h4 class="mb-0 fs-7 fw-semibold">${{ number_format($analyticsEanings->sum('total')) }}</h4>--}}
{{--                                            <p class="fs-3 mb-0">Total ganancias</p>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div>--}}
{{--                                        <div class="d-flex align-items-baseline mb-4">--}}
{{--                                            <span class="round-8 bg-primary rounded-circle me-6"></span>--}}
{{--                                            <div>--}}
{{--                                                <p class="fs-3 mb-1">Ordenes acuerdo</p>--}}
{{--                                                <h6 class="fs-5 fw-semibold mb-0">{{ $analyticsEaning->where('method_id', 1)->count() }}</h6>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="d-flex align-items-baseline mb-4 pb-1">--}}
{{--                                            <span class="round-8 bg-secondary rounded-circle me-6"></span>--}}
{{--                                            <div>--}}
{{--                                                <p class="fs-3 mb-1">Ordenes online</p>--}}
{{--                                                <h6 class="fs-5 fw-semibold mb-0">{{ $analyticsEaning->where('method_id', 2)->count() }}</h6>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div>--}}
{{--                                            <a href="{{ route('manager.orders') }}" class="btn btn-primary w-100">Visualiar ordenes</a>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="col-lg-12 d-flex align-items-strech">--}}
{{--                    <div class="card w-100">--}}
{{--                        <div class="card-body">--}}
{{--                            <div>--}}
{{--                                <h5 class="card-title fw-semibold mb-1">Ordenes</h5>--}}
{{--                                <p class="card-subtitle mb-0">Detalle ordenes por mes</p>--}}
{{--                                <div id="orders" class="mb-7 pb-8"></div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
                <!--  Row 3 -->
                <div class="row">
                    <!-- Weekly Stats -->
                    <div class="col-lg-12 d-flex align-items-strech">
                        <div class="card w-100">
                            <div class="card-body">
                                <div class="d-sm-flex d-block align-items-center justify-content-between mb-7">
                                    <div class="mb-3 mb-sm-0">
                                        <h5 class="card-title fw-semibold">Solicitudes contacto</h5>
                                        <p class="card-subtitle mb-0">Detalle de las últimas solicitudes de soporte</p>
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
                                            <span class="badge {{ $contact->reviewed == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-3 py-2 fw-semibold fs-2 d-inline-flex align-items-center gap-1">
                                                 {{ $contact->reviewed == 1 ? 'Gestionado' : 'Pendiente' }}
                                              </span>
                                                </td>
                                                <td class="">
                                                    <div class="dropdown">
                                                        <a class="text-decoration-none" href="{{ route('support.contacts.edit', $contact->slack) }}" >
                                                            <i class="fas fa-ellipsis-vertical fs-4"></i>
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
                <script src="{{ asset('supports/js/views/dashboard/index.js') }}"></script>
        @endpush
