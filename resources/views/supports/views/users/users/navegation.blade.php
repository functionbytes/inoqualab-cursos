@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => $user->firstname.' '.$user->lastname])
@endsection
@section('content')

            <div class="container-fluid">

                <div class="row justify-content-center users-nav-grid">
                    <div class="col-sm-6 col-lg-4">
                        <a class="card users-nav-card" href="{{ route('support.users.edit', $user->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">Perfil</span>
                                <div class="users-nav-icon">
                                    {!! \App\Html\IconHelper::render('user-settings', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Configuración</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card users-nav-card" href="{{ route('support.users.orders.index', $user->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['orders'] }} {{ Str::plural('orden', $counts['orders']) }}</span>
                                <div class="users-nav-icon">
                                    {!! \App\Html\IconHelper::render('user-orders', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Ordenes</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4 {{ $user->role == 'customer' ? '' : 'd-none' }}">
                        <a class="card users-nav-card" href="{{ route('support.users.inscriptions', $user->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['inscriptions'] }} {{ Str::plural('inscripción', $counts['inscriptions']) }}</span>
                                <div class="users-nav-icon">
                                    {!! \App\Html\IconHelper::render('user-inscriptions', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Inscripciones</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card users-nav-card" href="{{ route('support.users.courses.certificates', $user->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['certificates'] }} {{ Str::plural('certificado', $counts['certificates']) }}</span>
                                <div class="users-nav-icon">
                                    {!! \App\Html\IconHelper::render('user-certificates', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Certificados</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="card users-nav-card" href="{{ route('support.users.courses.results', $user->slack) }}">
                            <div class="card-body text-center">
                                <span class="badge bg-light-secondary text-muted d-inline-block mb-4">{{ $counts['results'] }} {{ Str::plural('resultado', $counts['results']) }}</span>
                                <div class="users-nav-icon">
                                    {!! \App\Html\IconHelper::render('user-results', 56) !!}
                                </div>
                                <h4 class="fw-bolder text-uppercase mb-3">Resultados</h4>
                            </div>
                        </a>
                    </div>

                </div>
            </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/users/users/navegation.css') }}">
@endpush
