@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        {{-- El empleado puede haber quedado sin distribuidor asociado (registro --}}
        {{-- huerfano en distributor_staff) — sin este fallback, $distributor->slack --}}
        {{-- lanza un 500 al renderizar la vista. --}}
        <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false"
              data-update-url="{{ route('support.distributors.staffs.update') }}"
              data-redirect-url="{{ ($distributor->slack ?? null) ? route('support.distributors.staffs', $distributor->slack) : route('support.distributors') }}">

          {{ csrf_field() }}

          <input type="hidden" id="id" name="id" value="{{ $user->id }}">
          <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">
          <input type="hidden" id="distributor" name="enterprise" value="{{ $distributor->slack ?? '' }}">
          <input type="hidden" id="edit" name="edit" value="true">

          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">
              <h5 class="mb-0">Editar empleado</h5>
            </div>
            <p class="card-subtitle mb-3 mt-3">
              Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos campos que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
            </p>

            <div class="row">

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Nombres</label>
                    <input type="text" class="form-control" id="firstname"  name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres" autocomplete="new-password" >
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Apellidos</label>
                    <input type="text" class="form-control" id="lastname"  name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido" autocomplete="new-password" >
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Identificación</label>
                    <input type="text" class="form-control" id="identification"  name="identification" value="{{ $user->identification }}" placeholder="Ingresar identificación" autocomplete="new-password" >
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Correo electronico</label>
                    <input type="text" class="form-control" id="email"  name="email" value="{{ $user->email }}" placeholder="Ingresar correo electronico" autocomplete="new-password" >
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Dirección</label>
                    <input type="text" class="form-control" id="address"  name="address" value="{{ $user->address }}" placeholder="Ingresar dirección" autocomplete="new-password" >
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Celular</label>
                    <input type="text" class="form-control" id="cellphone"  name="cellphone" value="{{ $user->cellphone }}" placeholder="Ingresar celular" autocomplete="new-password" >
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password"  name="password" value="" placeholder="Ingresar contraseña" autocomplete="new-password" >
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Estado</label>
                  <div class="input-group">
                    {!! Form::select('available', $availables, $user->available , ['class' => 'select2 form-control','id' => 'available']) !!}
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

@endsection



@push('scripts')
    <script src="{{ asset('supports/js/views/distributors/staffs/edit.js') }}"></script>
@endpush



