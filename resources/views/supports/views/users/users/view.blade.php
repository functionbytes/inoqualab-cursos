@extends('layouts.managers')


@php ob_start(); @endphp
Visualizar
                                @if ($user->role == 'manager')
                                    administrador
                                @elseif($user->role == 'customer')
                                    cliente
                                @elseif($user->role == 'enterprise')
                                    empresa
                                @elseif($user->role == 'distributor')
                                    distribuidor
                                @elseif($user->role == 'accounting')
                                    contabilidad
                                @endif
                            @php $__pageTitle = trim(preg_replace('/\s+/', ' ', ob_get_clean())); @endphp

@section('page_header')
    @include('supports.includes.card', ['title' => $__pageTitle])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}



                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Visualizar
                            @if ($user->role == 'manager')
                                administrador
                            @elseif($user->role == 'customer')
                                cliente
                            @elseif($user->role == 'enterprise')
                                empresa
                            @elseif($user->role == 'distributor')
                                distribuidor
                            @elseif($user->role == 'accounting')
                                contabilidad
                            @endif
                        </h6>
                        <p class="text-muted small mb-0">
                            Información general de esta cuenta. Estos datos son de solo lectura desde
                            esta pantalla.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Nombres</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Apellidos</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Identificación</label>
                                <input type="text" class="form-control" id="identification" name="identification" value="{{ $user->identification }}" placeholder="Ingresar identificación" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Correo electronico</label>
                                <input type="text" class="form-control" id="email" name="email" value="{{ $user->email }}" placeholder="Ingresar correo electronico" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Celular</label>
                                <input type="text" class="form-control" id="cellphone" name="cellphone" value="{{ $user->cellphone }}" placeholder="Ingresar celular" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Dirección</label>
                                <input type="text" class="form-control" id="address" name="address" value="{{ $user->address }}" placeholder="Ingresar dirección" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado</label>
                                <input type="text" class="form-control" id="available" name="available" value="{{ $user->available ? 'Activo' : 'Inactivo' }}" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Perfil</label>
                                @php
                                    $roleLabels = [
                                        'manager' => 'Administrador',
                                        'customer' => 'Cliente',
                                        'enterprise' => 'Empresa',
                                        'distributor' => 'Empleado distribuidor',
                                        'support' => 'Soporte',
                                        'accounting' => 'Contabilidad',
                                    ];
                                @endphp
                                <input type="text" class="form-control" id="role" name="role" value="{{ $roleLabels[$user->role] ?? 'Sin rol' }}" disabled>
                            </div>
                        </div>

                    </div>


                </form>
            </div>

        </div>

    </div>

@endsection


