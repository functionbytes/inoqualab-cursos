@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formDistributors" enctype="multipart/form-data" role="form" onSubmit="return false"
              data-update-url="{{ route('support.distributors.update') }}"
              data-redirect-url="{{ route('support.distributors') }}">

          {{ csrf_field() }}

          <input type="hidden" id="slack" name="slack" value="{{ $distributor->slack }}">


          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">
              <h5 class="mb-0">Editar distribuidor</h5>
            </div>
            <p class="card-subtitle mb-3 mt-3">
              Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos campos que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
            </p>

            <div class="row">

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Titulo</label>
                  <input type="text" class="form-control" id="title"  name="title"  value="{{ $distributor->title }}"   placeholder="Ingresa titulo" autocomplete="new-password" >
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Nit</label>
                  <input type="text" class="form-control" id="nit"  name="nit"  value="{{ $distributor->nit }}"  placeholder="Ingresa nit" autocomplete="new-password" >
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Celular</label>
                  <input type="text" class="form-control" id="cellphone"  name="cellphone" value="{{ $distributor->cellphone }}"   placeholder="Ingresa telefono" autocomplete="new-password" >
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Dirección</label>
                  <input type="text" class="form-control" id="address"  name="address" value="{{ $distributor->address }}"   placeholder="Ingresa dirección" autocomplete="new-password" >
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Estado</label>
                  <div class="input-group">
                    {!! Form::select('available', $availables, $distributor->available , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
                  </div>
                  <label id="available-error" class="error d-none" for="available"></label>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Permisos empresa</label>
                  <div class="input-group">
                    {!! Form::select('enterprise_generate', $generates, $distributor->enterprise_generate , ['class' => 'select2 form-control' ,'name' => 'enterprise_generate', 'id' => 'enterprise_generate' ]) !!}
                  </div>
                  <label id="enterprise_generate-error" class="error d-none" for="available"></label>
                </div>
              </div>
              <div class="col-12">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Correo electronico</label>
                  <input type="text" class="form-control" id="email"  name="email" value="{{ $distributor->email }}"  placeholder="Ingresa correo electronico" autocomplete="new-password" >
                </div>
              </div>


              <div class="col-12">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Soporte</label>
                  <input type="text" class="form-control" id="supporting"  name="supporting"  value="{{ $distributor->supporting }}" placeholder="Ingresa el encargado de soporte" autocomplete="new-password" >
                </div>
              </div>

              <div class="col-12">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Gerente</label>
                  <input type="text" class="form-control" id="leading"  name="leading"  value="{{ $distributor->leading }}" placeholder="Ingresa el encargado de gerente" autocomplete="new-password" >
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
    <script src="{{ asset('supports/js/views/distributors/distributors/edit.js') }}"></script>
@endpush
