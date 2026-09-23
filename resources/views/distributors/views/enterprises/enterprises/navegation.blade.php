@extends('layouts.managers')


@section('page_header')
    @include('distributors.includes.card', ['title' => $enterprise->title])
@endsection
@section('content')

            <div class="container-fluid">

                <div class="row justify-content-center nav-cards-grid">
                    <div class="col-sm-6 col-lg-4">
                        <a class="card nav-cards-card" href="{{ route('distributor.enterprises.users', $enterprise->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['users'] }} {{ Str::plural('usuario', $counts['users']) }}</span>
                                <div class="nav-cards-icon">
                                    {!! \App\Html\IconHelper::render('nav-people', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Usuarios</h4>
                            </div>
                        </a>
                    </div>
                    @if($distributor->enterprise_generate)
                        <div class="col-sm-6 col-lg-4">
                            <a class="card nav-cards-card" href="{{ route('distributor.enterprises.staffs', $enterprise->slack) }}">
                                <div class="card-body text-center">
                                    <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['staffs'] }} {{ Str::plural('empleado', $counts['staffs']) }}</span>
                                    <div class="nav-cards-icon">
                                        {!! \App\Html\IconHelper::render('nav-people', 56) !!}
                                    </div>
                                    <h4 class="fw-bolder text-uppercase mb-3">Empleados</h4>
                                </div>
                            </a>
                        </div>
                    @endif
                    <div class="col-sm-6 col-lg-4">
                        <a class="card nav-cards-card" href="{{ route('distributor.enterprises.courses', $enterprise->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['courses'] }} {{ Str::plural('curso', $counts['courses']) }}</span>
                                <div class="nav-cards-icon">
                                    {!! \App\Html\IconHelper::render('nav-courses', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Cursos</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card nav-cards-card" href="{{ route('distributor.enterprises.users.income', $enterprise->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">Ingresos</span>
                                <div class="nav-cards-icon">
                                    {!! \App\Html\IconHelper::render('user-results', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Reporte</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card nav-cards-card" href="{{ route('distributor.enterprises.inscriptions', $enterprise->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">Matrícula</span>
                                <div class="nav-cards-icon">
                                    {!! \App\Html\IconHelper::render('user-inscriptions', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Inscripciones</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card nav-cards-card" href="{{ route('distributor.enterprises.reassign', $enterprise->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">Traslados</span>
                                <div class="nav-cards-icon">
                                    {!! \App\Html\IconHelper::render('nav-reassign', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Reasignación de usuarios</h4>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('distributors/css/views/enterprises/enterprises/navegation.css') }}">
@endpush
