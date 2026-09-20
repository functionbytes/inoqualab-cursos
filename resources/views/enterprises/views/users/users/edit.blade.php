@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false"
                      data-update-url="{{ route('enterprise.users.update') }}"
                      data-redirect-url="{{ route('enterprise.users') }}">

                    {{ csrf_field() }}

                    <input type="hidden" id="id" name="id" value="{{ $user->id }}">
                    <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">
                    <input type="hidden" id="edit" name="edit" value="true">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar cliente</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos campos que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
                        </p>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Nombres</label>
                                        <input type="text" class="form-control" id="firstname"  name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Apellidos</label>
                                        <input type="text" class="form-control" id="lastname"  name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido" disabled> 
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Identificación</label>
                                        <input type="text" class="form-control" id="identification"  name="identification" value="{{ $user->identification }}" placeholder="Ingresar identificación" disabled>
                                    
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Correo electronico</label>
                                        <input type="text" class="form-control" id="email"  name="email" value="{{ $user->email }}" placeholder="Ingresar correo electronico" disabled>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Celular</label>
                                        <input type="text" class="form-control" id="cellphone"  name="cellphone" value="{{ $user->cellphone }}" placeholder="Ingresar celular">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                        <label  class="control-label col-form-label">Dirección</label>
                                        <input type="text" class="form-control" id="address" name="address" value="{{ $user->address }}" placeholder="Ingresar dirección">
                                </div>
                            </div>
                            <div class="col-12">
                                 <div class="action-form border-top">
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-info rounded-pill px-4 waves-effect waves-light">
                                            Guardar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ asset('enterprises/js/views/users/users/edit.js') }}"></script>
@endpush



