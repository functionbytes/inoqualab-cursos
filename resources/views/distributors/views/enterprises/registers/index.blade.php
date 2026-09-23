@extends('layouts.managers')


@section('page_header')
    @include('distributors.includes.card', ['title' => 'Registro'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formRegisters" enctype="multipart/form-data" role="form" onSubmit="return false"
                  data-check-url="{{ route('distributor.registers.users.check') }}"
                  data-store-url="{{ route('distributor.registers.store') }}"
                  data-enterprise-users-url-template="{{ route('distributor.enterprises.users', ':slack') }}">

                {{ csrf_field() }}

                <input id="distributor" name="distributor" type="hidden" value="{{ $distributor->slack }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Registro</h6>
                        <p class="text-muted small mb-0">
                            Registra un nuevo usuario para una de tus empresas. Selecciona la empresa
                            y completa los datos del usuario para crear su cuenta y poder matricularlo
                            en los cursos disponibles.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Empresa</label>
                                {!! Form::select('enterprise', $enterprises, null , ['class' => 'select2 form-control' ,'name' => 'enterprise', 'id' => 'enterprise']) !!}
                                <label id="enterprise-error" class="error d-none" for="enterprise"></label>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombres</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" value="" autocomplete="new-password" placeholder="Ingresar nombres">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Apellidos</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" value="" autocomplete="new-password" placeholder="Ingresar apellido">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Identificación</label>
                                <input type="text" class="form-control" id="identification" name="identification" value="" autocomplete="new-password" placeholder="Ingresar identificación">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Dirección</label>
                                <input type="text" class="form-control" id="address" name="address" value="" autocomplete="new-password" placeholder="Ingresar dirección">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Correo electronico</label>
                                <input type="text" class="form-control" id="email" name="email" value="" autocomplete="new-password" placeholder="Ingresar correo electronico">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Celular</label>
                                <input type="text" class="form-control" id="cellphone" name="cellphone" value="" autocomplete="new-password" placeholder="Ingresar celular">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" value="" autocomplete="new-password" placeholder="Ingresar contraseña">
                            </div>

                            <div class="col-12">
                                <div class="errors d-none"></div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar
                        </button>
                    </div>

                </div>
            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre el registro</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        El usuario queda asociado a la empresa seleccionada y puede matricularse en
                        los cursos disponibles para esa empresa.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <div id="users-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-danger"><i class="fas fa-circle-xmark"></i></div>
                    <h4 class="my-0">Confirmacion de registro</h4>
                    <p>Deseas crear un nuevo usuario</p>
                    <div class="row justify-content-center mt-20  ">
                        <div class="col-sm-12 col-md-5 enterprise-div d-none">
                            <a href="" id="enterprise-link" class="btn btn-danger w-100">Empresa</a>
                        </div>
                        <div class="col-sm-12 col-md-5">
                            <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Seguir creando</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="message-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-danger"><i class="fas fa-circle-xmark"></i></div>
                    <h4 class="my-0 modal-content-message">¿Deseas seguir creando usuarios o retornar a la empresa?</h4>
                    <p class="modal-content-description"></p>
                    <div class="row justify-content-center mt-20  ">
                        <div class="col-sm-12 col-md-5 reassign-div d-none w-100 mt-1">
                            <a href="" id="reassign-links" class="btn btn-danger w-100">Reasignar</a>
                        </div>
                        <div class="col-sm-12 col-md-5 enterprise-div d-none w-100 mt-1 ">
                            <a href="" id="enterprise-links" class="btn btn-danger w-100">Empresa</a>
                        </div>
                        <div class="col-sm-12 col-md-5 w-100 mt-1">
                            <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @endsection




    @push('scripts')
        <script src="{{ asset('distributors/js/enterprises/registers/index.js') }}"></script>
    @endpush


