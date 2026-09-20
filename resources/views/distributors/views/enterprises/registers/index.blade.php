@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">
      
            <div class="card w-100">

                <form id="formRegisters" enctype="multipart/form-data" role="form" onSubmit="return false"
                      data-check-url="{{ route('distributor.registers.users.check') }}"
                      data-store-url="{{ route('distributor.registers.store') }}"
                      data-enterprise-users-url-template="{{ route('distributor.enterprises.users', ':slack') }}">

                    {{ csrf_field() }}

                    <input id="distributor" name="distributor" type="hidden" value="{{ $distributor->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Registro</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera
                            eficiente y segura. A continuación, encontrarás diversos campos que
                            corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier
                            información que consideres necesario actualizar para mantener tus datos al día.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Empresa</label>
                                    <div class="input-group">
                                        {!! Form::select('enterprise', $enterprises, null , ['class' => 'select2 form-control' ,'name' => 'enterprise', 'id' => 'enterprise']) !!}
                                    </div>
                                    <label id="enterprise-error" class="error d-none" for="enterprise"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Nombres</label>
                                    <input type="text" class="form-control" id="firstname"  name="firstname" value="" autocomplete="new-password"placeholder="Ingresar nombres">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Apellidos</label>
                                    <input type="text" class="form-control" id="lastname"  name="lastname" value="" autocomplete="new-password"placeholder="Ingresar apellido">
                                </div>
                            </div>
    
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Identificación</label>
                                    <input type="text" class="form-control" id="identification"  name="identification" value="" autocomplete="new-password" placeholder="Ingresar identificación">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Dirección</label>
                                    <input type="text" class="form-control" id="address"  name="address" value="" autocomplete="new-password" placeholder="Ingresar dirección">
                                </div>
                            </div>
    
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Correo electronico</label>
                                    <input type="text" class="form-control" id="email"  name="email" value="" autocomplete="new-password" placeholder="Ingresar correo electronico">
                                </div>
                            </div>

    
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Celular</label>
                                    <input type="text" class="form-control" id="cellphone"  name="cellphone" value="" autocomplete="new-password"placeholder="Ingresar celular">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password"  name="password" value="" autocomplete="new-password"placeholder="Ingresar contraseña">
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
    
    
    