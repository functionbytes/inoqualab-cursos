@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formUsers" enctype="multipart/form-data" role="form"
              data-update-url="{{ route('manager.enterprises.users.update') }}"
              data-navegation-url="{{ route('manager.enterprises.users', ':slack') }}"
              data-enterprise-slack="{{ $enterprise->slack }}">

          {{ csrf_field() }}

          <input type="hidden" id="id" name="id" value="{{ $user->id }}">
          <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">
          <input type="hidden" id="$enterprise" name="enterprise" value="{{ $enterprise->slack }}">
          <input type="hidden" id="edit" name="edit" value="true">

          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">

              <h5 class="mb-0">Editar
                @if ($user->role == 'manager')
                  administrador
                @elseif($user->role == 'customer')
                  cliente
                @elseif($user->role == 'enterprises')
                  empresa
                @endif
              </h5>

            </div>
            <p class="card-subtitle mb-3 mt-3">
              Actualiza los datos del usuario. Los cambios se guardarán al hacer clic en Guardar.
            </p>

            <div class="row">

              <div class="col-6">
                <div class="mb-3">
                  <div class="mb-3">
                    <label  class="control-label col-form-label">Nombres</label>
                    <input type="text" class="form-control" id="firstname"  name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres">
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                  <div class="mb-3">
                    <label  class="control-label col-form-label">Apellidos</label>
                    <input type="text" class="form-control" id="lastname"  name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido">
                  </div>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <div class="mb-3">
                    <label  class="control-label col-form-label">Identificación</label>
                    <input type="text" class="form-control" id="identification"  name="identification" value="{{ $user->identification }}" placeholder="Ingresar identificación">
                  </div>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <div class="mb-3">
                    <label  class="control-label col-form-label">Correo electronico</label>
                    <input type="text" class="form-control" id="email"  name="email" value="{{ $user->email }}" placeholder="Ingresar correo electronico">
                  </div>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <div class="mb-3">
                    <label  class="control-label col-form-label">Dirección</label>
                    <input type="text" class="form-control" id="address"  name="address" value="{{ $user->address }}" placeholder="Ingresar dirección">
                  </div>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <div class="mb-3">
                    <label  class="control-label col-form-label">Celular</label>
                    <input type="text" class="form-control" id="cellphone"  name="cellphone" value="{{ $user->cellphone }}" placeholder="Ingresar celular">
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                  <div class="mb-3">
                    <label  class="control-label col-form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password"  name="password" value="" placeholder="Ingresar contraseña">
                  </div>
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
<script src="{{ asset('managers/js/views/enterprises/users/edit.js') }}"></script>
@endpush



