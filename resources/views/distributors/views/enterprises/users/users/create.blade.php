@extends('layouts.managers')


@section('page_header')
    @include('distributors.includes.card', ['title' => 'Crear usuario'])
@endsection
@section('content')

<div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

        <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false"
              data-check-url="{{ route('distributor.enterprises.users.check') }}"
              data-store-url="{{ route('distributor.enterprises.users.store') }}"
              data-redirect-url="{{ route('distributor.enterprises.users', $enterprise->slack) }}">

            {{ csrf_field() }}

            <input type="hidden" id="id" name="id" value="">
            <input type="hidden" id="slack" name="slack" value="">
            <input type="hidden" id="edit" name="edit" value="true">
            <input type="hidden" id="enterprise" name="enterprise" value="{{ $enterprise->slack }}">

            <div class="card">

                <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Crear usuario</h6>
                    <p class="text-muted mb-3">
                        Completa los datos para registrar un nuevo usuario de la empresa.
                    </p>

                    <div class="row g-3">

                        <div class="col-6">
                            <label class="form-label fw-semibold">Nombres</label>
                            <input type="text" class="form-control" id="firstname"  name="firstname" value="" autocomplete="new-password" placeholder="Ingresar nombres">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Apellidos</label>
                            <input type="text" class="form-control" id="lastname"  name="lastname" value="" autocomplete="new-password" placeholder="Ingresar apellido">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Identificación</label>
                            <input type="text" class="form-control" id="identification"  name="identification" value="" autocomplete="new-password" placeholder="Ingresar identificación">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Correo electronico</label>
                            <input type="text" class="form-control" id="email"  name="email" value="" autocomplete="new-password" placeholder="Ingresar correo electronico">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Dirección</label>
                            <input type="text" class="form-control" id="address"  name="address" value="" autocomplete="new-password" placeholder="Ingresar dirección">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Celular</label>
                            <input type="text" class="form-control" id="cellphone"  name="cellphone" value="" autocomplete="new-password" placeholder="Ingresar celular">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Contraseña</label>
                            <input type="password" class="form-control" id="password"  name="password" value="" autocomplete="new-password" placeholder="Ingresar contraseña">
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
                <h6 class="mb-0 fw-bold">Sobre los usuarios</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">El usuario quedará asociado a esta empresa y podrá acceder con el correo y la contraseña que definas.</p>
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
                <h4 class="my-0">Proceso realizado</h4>
                <p>¿Deseas crear un nuevo usuario?</p>
                <div class="row justify-content-center mt-20  ">
                    <div class="col-sm-12 col-md-5">
                        <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Si</button>
                    </div>
                    <div class="col-sm-12 col-md-5">
                        <a href="" id="users-link" class="btn btn-danger w-100">No</a>
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
                    <div class="col-sm-12 col-md-5 enterprise-div d-none">
                        <a href="" id="enterprise-link" class="btn btn-danger w-100">Empresa</a>
                    </div>
                    <div class="col-sm-12 col-md-5">
                        <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection



@push('scripts')
    <script src="{{ asset('distributors/js/enterprises/users/users/create.js') }}"></script>
@endpush


