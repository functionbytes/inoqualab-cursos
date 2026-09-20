@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false">

                    {{ csrf_field() }}

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">

                            <h5 class="mb-0">Visualizar
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
                                @elseif($user->role == 'support')
                                    soporte
                                @endif
                            </h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas revisar la información de este usuario de forma segura. A continuación encontrarás los datos previamente suministrados.
                        </p>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Nombres</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Apellidos</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Identificación</label>
                                    <input type="text" class="form-control" id="identification" name="identification" value="{{ $user->identification }}" placeholder="Ingresar identificación" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Correo electrónico</label>
                                    <input type="text" class="form-control" id="email" name="email" value="{{ $user->email }}" placeholder="Ingresar correo electrónico" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Celular</label>
                                    <input type="text" class="form-control" id="cellphone" name="cellphone" value="{{ $user->cellphone }}" placeholder="Ingresar celular" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Dirección</label>
                                    <input type="text" class="form-control" id="address" name="address" value="{{ $user->address }}" placeholder="Ingresar dirección" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Estado</label>
                                    <input type="text" class="form-control" id="available" name="available" value="{{ $user->available ? 'Activo' : 'Inactivo' }}" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Perfil</label>
                                    <input type="text" class="form-control" id="role" name="role" value="{{ $roles[$user->role] ?? 'Sin rol' }}" disabled>
                                </div>
                            </div>
                        </div>

                        <a href="{{ url()->previous() }}" class="btn btn-light">
                            Volver
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>

@endsection
