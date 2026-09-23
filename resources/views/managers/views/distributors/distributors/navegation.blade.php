@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => $distributor->title])
@endsection
@section('content')

            <div class="container-fluid">

                <div class="row justify-content-center nav-cards-grid">
                    <div class="col-sm-6 col-lg-3">
                        <a class="card nav-cards-card" href="{{ route('manager.distributors.enterprises', $distributor->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['enterprises'] }} {{ Str::plural('empresa', $counts['enterprises']) }}</span>
                                <div class="nav-cards-icon">
                                    {!! \App\Html\IconHelper::render('nav-enterprises', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Empresas</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="card nav-cards-card" href="{{ route('manager.distributors.staffs', $distributor->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['staffs'] }} {{ Str::plural('empleado', $counts['staffs']) }}</span>
                                <div class="nav-cards-icon">
                                    {!! \App\Html\IconHelper::render('nav-people', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Empleados</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <a class="card nav-cards-card" href="{{ route('manager.distributors.courses', $distributor->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['courses'] }} {{ Str::plural('curso', $counts['courses']) }}</span>
                                <div class="nav-cards-icon">
                                    {!! \App\Html\IconHelper::render('nav-courses', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Cursos</h4>
                            </div>
                        </a>
                    </div>
                    @if($counts['rates'] > 0)
                    <div class="col-sm-6 col-lg-3">
                        <a class="card nav-cards-card" href="{{ route('manager.distributors.rates', $distributor->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['rates'] }} {{ Str::plural('tarifa', $counts['rates']) }}</span>
                                <div class="nav-cards-icon">
                                    {!! \App\Html\IconHelper::render('nav-rates', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Tarifas</h4>
                            </div>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/distributors/distributors/navegation.css') }}">
@endpush
