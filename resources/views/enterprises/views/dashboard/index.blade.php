@extends('layouts.managers')

@section('content')
        <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
                <div class="card w-100 ">
                    <div class="card-body position-relative">
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="mb-7 mt-6">
                                    <h2 class="fw-semibold mb-1 text-uppercase">Bienvenido de nuevo a tu tablero!</h2>
                                    <p>Aquí encontrarás un resumen detallado de tus usuarios, cursos y certificaciones. Desde esta plataforma
                                        intuitiva, podrás explorar y gestionar tus cursos de manera eficiente, manteniéndote al
                                        día con tus metas de aprendizaje.</p>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="welcome-bg-img mb-n7 text-end">
                                    <img src="/customers/images/dashboard/dashboard.svg" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
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
                                <h5 class="card-title fw-semibold">Resporte usuarios</h5>
                                <p class="card-subtitle mb-0">Detalle de las ultias solcitudes de soporte</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle text-nowrap mb-0">
                                <thead>
                                    <tr class="text-muted fw-semibold">
                                        <th scope="col">Identificación</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Correo electronico</th>
                                        <th scope="col">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top">
                                    @foreach ($users as $key => $user)
                                        <tr>
                                            <td>
                                                <span class="usr-email-addr" data-email="{{ $user->identification }}">{{ ucfirst($user->identification) }}</span>
                                            </td>
                                            <td>
                                                <span class="usr-email-addr" data-email="{{ $user->firstname . ' ' . $user->lastname }}">{{ Str::words(
                                                    Str::upper(Str::lower($user->firstname . ' ' . $user->lastname)), 12, '...') }}</span>
                                            </td>
                                            <td>
                                                <span class="usr-email-addr" data-email="{{ $user->email }}">{{ $user->email }}</span>
                                            </td>
                                            <td>
                                                <span class="usr-ph-no" data-phone="{{ date('Y-m-d', strtotime($user->updated_at)) }}">{{ date('Y-m-d',
                                                    strtotime($user->updated_at)) }}</span>
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

@endsection

@push('scripts')


@endpush