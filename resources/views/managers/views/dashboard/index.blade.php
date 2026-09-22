@extends('layouts.managers')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Dashboard'])
@endsection

@section('content')

    <div class="widget-content" id="dashboard-index"
         data-range="{{ $range }}"
         data-routes='@php $__jsonInline1 = [
            "overview" => route("manager.dashboard.overview"),
            "realtime" => route("manager.analytics.realtime"),
         ]; @endphp@json($__jsonInline1)'>

        {{-- Filters bar --}}
        <div class="card card-body mb-4 border-0 shadow-sm">
            <div class="row align-items-center g-2">

                <div class="col-auto">
                    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-2 bg-primary-subtle">
                        <span class="realtime-dot position-relative d-flex align-items-center justify-content-center rounded-circle">
                            <span class="realtime-dot-badge position-absolute rounded-circle bg-success border border-white"></span>
                        </span>
                        <div>
                            <div class="d-flex align-items-baseline gap-1 lh-1 mb-1">
                                <span class="fw-bold text-primary" id="realtime-count">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </span>
                                <span class="small fw-semibold text-primary">en línea</span>
                            </div>
                            <div class="lh-1 text-muted realtime-updated-text" id="realtime-updated"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-auto d-none d-md-block">
                    <div class="divider-vertical"></div>
                </div>

                <div class="col dashboard-pills-col">
                    <ul class="nav nav-pills gap-1 flex-nowrap overflow-auto" id="rangePills">
                        @foreach([
                            'today'        => 'Hoy',
                            'last_7_days'  => '7 días',
                            'last_30_days' => '30 días',
                            'this_month'   => 'Este mes',
                            'last_month'   => 'Mes anterior',
                            'this_year'    => 'Este año',
                        ] as $key => $label)
                        <li class="nav-item flex-shrink-0">
                            <a class="nav-link py-1 px-3 small fw-semibold {{ $range === $key ? 'active' : 'text-muted' }}"
                               href="#" data-range="{{ $key }}">{{ $label }}</a>
                        </li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>

        {{-- KPI cards --}}
        <div class="row mb-4 g-3" id="dashboard-kpi-row">

            <div class="col-6 col-lg-4">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Cursos</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-courses"><span class="spinner-border spinner-border-sm text-muted"></span></h4>
                                <div id="cmp-courses"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-courses"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-4">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Empresas</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-enterprises"><span class="spinner-border spinner-border-sm text-muted"></span></h4>
                                <div id="cmp-enterprises"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-enterprises"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-4">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Blogs</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-blogs"><span class="spinner-border spinner-border-sm text-muted"></span></h4>
                                <div id="cmp-blogs"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-blogs"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-4">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Clientes</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-customers"><span class="spinner-border spinner-border-sm text-muted"></span></h4>
                                <div id="cmp-customers"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-customers"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-4">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h5 class="card-title fw-semibold mb-3">Administradores</h5>
                                <h4 class="fw-semibold mb-2" id="kpi-managers"><span class="spinner-border spinner-border-sm text-muted"></span></h4>
                                <div id="cmp-managers"></div>
                            </div>
                            <div class="col-4 d-flex justify-content-center">
                                <div id="spark-managers"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-3">
            <div class="col-lg-12">
                <div class="card w-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Detalle facturación</h4>
                        <p class="card-subtitle mt-1">Ganancias por ordenes del período seleccionado</p>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-lg-8 col-md-8">
                                <div id="billing-chart" class="dashboard-main-chart"></div>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="d-flex align-items-center mb-4 pb-1">
                                    <div>
                                        <h4 class="mb-0 fs-7 fw-semibold" id="billing-total">$0</h4>
                                        <p class="fs-3 mb-0">Total ganancias</p>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex align-items-baseline mb-4">
                                        <span class="round-8 bg-primary rounded-circle me-6"></span>
                                        <div>
                                            <p class="fs-3 mb-1">Ordenes acuerdo</p>
                                            <h6 class="fs-5 fw-semibold mb-0" id="billing-agreement">0</h6>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-baseline mb-4 pb-1">
                                        <span class="round-8 bg-secondary rounded-circle me-6"></span>
                                        <div>
                                            <p class="fs-3 mb-1">Ordenes online</p>
                                            <h6 class="fs-5 fw-semibold mb-0" id="billing-online">0</h6>
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

            <div class="col-lg-12">
                <div class="card w-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Ordenes</h4>
                        <p class="card-subtitle mt-1">Detalle de ordenes del período seleccionado</p>
                    </div>
                    <div class="card-body">
                        <div id="orders-chart" class="dashboard-main-chart"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card w-100">
                    <div class="card-header">
                        <h4 class="card-title fw-semibold mb-0">Solicitudes de contacto</h4>
                        <p class="card-subtitle mt-1">Detalle de las ultimas solicitudes de soporte</p>
                    </div>
                    <div class="card-body">
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
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.contacts.edit', $contact->slack) }}">
                                                            Ver contacto
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
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/dashboard/index.css') }}">
@endpush

@push('scripts')
    <script src="{{ url('managers/libs/owl.carousel/dist/owl.carousel.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('managers/libs/apexcharts/dist/apexcharts.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('managers/js/views/dashboard/index.js') }}"></script>
@endpush
