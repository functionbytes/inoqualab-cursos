@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Crear usuario'])
@endsection
@section('content')

<div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

        <form id="formUsers" enctype="multipart/form-data" role="form"
              data-check-url="{{ route('support.enterprises.users.check') }}"
              data-store-url="{{ route('support.enterprises.users.store') }}"
              data-redirect-url="{{ route('support.enterprises.users', $enterprise->slack) }}">

            {{ csrf_field() }}

            <input type="hidden" id="id" name="id" value="">
            <input type="hidden" id="slack" name="slack" value="">
            <input type="hidden" id="edit" name="edit" value="true">
            <input type="hidden" id="enterprise" name="enterprise" value="{{ $enterprise->slack }}">

            <div class="card">

                <div class="card-header border-bottom">
                    <h6 class="mb-1 fw-bold">Crear usuario</h6>
                    <p class="text-muted small mb-0">
                        Completa los datos del usuario. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
                    </p>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-6">
                            <label class="form-label fw-semibold">Nombres</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" value="" placeholder="Ingresar nombres" autocomplete="new-password">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Apellidos</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" value="" placeholder="Ingresar apellido" autocomplete="new-password">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Identificación</label>
                            <input type="text" class="form-control" id="identification" name="identification" value="" placeholder="Ingresar identificación" autocomplete="new-password">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Correo electronico</label>
                            <input type="text" class="form-control" id="email" name="email" value="" placeholder="Ingresar correo electronico" autocomplete="new-password">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Dirección</label>
                            <input type="text" class="form-control" id="address" name="address" value="" placeholder="Ingresar dirección" autocomplete="new-password">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Celular</label>
                            <input type="text" class="form-control" id="cellphone" name="cellphone" value="" placeholder="Ingresar celular" autocomplete="new-password">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" value="" placeholder="Ingresar contraseña" autocomplete="new-password">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Estado</label>
                            <div class="input-group">
                                {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                            </div>
                            <label id="available-error" class="error d-none" for="available"></label>
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
                <p class="text-muted mb-0">El usuario queda asociado a la empresa actual y tendrá acceso a los cursos e inscripciones que esta le asigne.</p>
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
    <script src="{{ asset('supports/js/enterprises/users/users/create.js') }}"></script>
@endpush



