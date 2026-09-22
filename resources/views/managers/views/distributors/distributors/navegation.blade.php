@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => $distributor->title])
@endsection
@section('content')

            <div class="container-fluid">

                <div class="row justify-content-center navegation-content">
                    <div class="col-lg-12 text-center">
                        <span class="fw-bolder text-uppercase fs-2 d-block mb-1">EMPRESA</span>
                            <h3 class="fw-bolder mb-0 fs-8 lh-base">{{ $distributor->title }}</h3>
                    </div>
                </div>

                <div class="row justify-content-center mt--20">
                    <div class="col-sm-6 col-lg-3">
                        <a class="card" href="{{ route('manager.distributors.enterprises', $distributor->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <i class="font-navegation fa-sharp-duotone fa-solid fa-house-blank"></i>
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Empresas</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="card" href="{{ route('manager.distributors.staffs', $distributor->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <i class="font-navegation fa-duotone fa-user-vneck-hair"></i>
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Empleados</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="card" href="{{ route('manager.distributors.courses', $distributor->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <i class="font-navegation fa-duotone fa-ballot-check"></i>
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Cursos</h4>
                            </div>
                        </a>
                    </div>
                    @if(count($distributor->rates) > 0)
                    <div class="col-sm-6 col-lg-3">
                        <a class="card" href="{{ route('manager.distributors.rates', $distributor->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <i class="font-navegation fa-duotone fa-circle-dollar"></i>
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Tarifas</h4>
                            </div>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

@endsection

