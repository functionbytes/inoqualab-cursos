@extends('layouts.managers')

@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

        <div class="card w-100">

            <form id="formUsers" enctype="multipart/form-data" role="form"
                  data-check-url="{{ route('support.enterprises.users.check') }}"
                  data-store-url="{{ route('support.enterprises.users.store') }}"
                  data-redirect-url="{{ route('support.enterprises.users', $enterprise->slack) }}">

                {{ csrf_field() }}

                <input type="hidden" id="id" name="id" value="">
                <input type="hidden" id="slack" name="slack" value="">
                <input type="hidden" id="edit" name="edit" value="true">
                <input type="hidden" id="enterprise" name="enterprise" value="{{ $enterprise->slack }}">

                <div class="card-body border-top">
                    <div class="d-flex no-block align-items-center">

                        <h5 class="mb-0">Crear usuario
                        </h5>

                    </div>
                    <p class="card-subtitle mb-3 mt-3">
                        Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos campos que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                    </p>

                    <div class="row">

                        <div class="col-6">
                            <div class="mb-3">
                                <label  class="control-label col-form-label">Nombres</label>
                                <input type="text" class="form-control" id="firstname"  name="firstname" value="" placeholder="Ingresar nombres" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label  class="control-label col-form-label">Apellidos</label>
                                <input type="text" class="form-control" id="lastname"  name="lastname" value="" placeholder="Ingresar apellido" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label  class="control-label col-form-label">Identificación</label>
                                <input type="text" class="form-control" id="identification"  name="identification" value="" placeholder="Ingresar identificación" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label  class="control-label col-form-label">Correo electronico</label>
                                <input type="text" class="form-control" id="email"  name="email" value="" placeholder="Ingresar correo electronico" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label  class="control-label col-form-label">Dirección</label>
                                <input type="text" class="form-control" id="address"  name="address" value="" placeholder="Ingresar dirección" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label  class="control-label col-form-label">Celular</label>
                                <input type="text" class="form-control" id="cellphone"  name="cellphone" value="" placeholder="Ingresar celular" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label  class="control-label col-form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password"  name="password" value="" placeholder="Ingresar contraseña" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label class="control-label col-form-label">Estado</label>
                                <div class="input-group">
                                    {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                                </div>
                                <label id="available-error" class="error d-none" for="available"></label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="errors d-none">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="border-top pt-1 mt-4">
                                <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                        Guardar
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </form>
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



