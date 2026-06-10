@extends('layouts.supports')

@section('content')

            <div class="container-fluid">

                <div class="row justify-content-center navegation-content">
                    <div class="col-lg-12 text-center">
                        <span class="fw-bolder text-uppercase fs-2 d-block mb-1">CLIENTE</span>
                            <h3 class="fw-bolder mb-0 fs-8 lh-base">{{ $user->firstname }} {{ $user->lastname }}</h3>
                    </div>
                </div>


                <div class="row justify-content-center mt--20">
                    <div class="col-sm-6 col-lg-4">
                        <a class="card" href="{{ route('support.users.edit', $user->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <i class="font-navegation fa-duotone fa-user-vneck-hair"></i>
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Configuraciòn</h4>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <a class="card" href="{{ route('support.users.orders.index', $user->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <i class="font-navegation fa-duotone fa-credit-card-front"></i>
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Ordenes</h4>
                            </div>
                        </a>
                    </div>


                    <div class="col-sm-6 col-lg-4 {{ $user->role == 'customer' ? '': 'd-none' }}">
                        <a class="card" href="{{ route('support.users.inscriptions', $user->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <i class="font-navegation fa-duotone fa-light fa-subtitles"></i>
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Inscripciones</h4>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <a class="card" href="{{ route('support.users.courses.certificates', $user->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <i class="font-navegation fa-duotone fa-ballot-check"></i>
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Certificados</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card" href="{{ route('support.users.courses.results', $user->slack) }}">
                            <div class="card-body text-center">
                                <span class="fw-bolder text-uppercase fs-2 d-block mb-7">Opción</span>
                                <div class="my-4">
                                    <i class="font-navegation fa-duotone fa-ballot-check"></i>
                                </div>
                                <h4 class="fw-bolder  text-uppercase mb-3">Resultados</h4>
                            </div>
                        </a>
                    </div>

                </div>
            </div>

@endsection

