@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formDistributors" enctype="multipart/form-data" role="form"
              data-store-url="{{ route('manager.distributors.store') }}"
              data-redirect-url="{{ route('manager.distributors') }}">

          {{ csrf_field() }}

          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">
              <h5 class="mb-0">Crear distribuidor</h5>
            </div>
            <p class="card-subtitle mb-3 mt-3">
              Completa los datos del distribuidor. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
            </p>
            <div class="row">

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Titulo</label>
                    <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Nit</label>
                    <input type="text" class="form-control" id="nit"  name="nit"  placeholder="Ingresa nit">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Celular</label>
                    <input type="text" class="form-control" id="cellphone"  name="cellphone"  placeholder="Ingresa telefono">
                </div>
              </div>


              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Correo electronico</label>
                  <input type="text" class="form-control" id="email"  name="email"  placeholder="Ingresa correo electronico">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Dirección</label>
                    <input type="text" class="form-control" id="address"  name="address"  placeholder="Ingresa dirección">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Permisos empresa</label>
                  <div class="input-group">
                    {!! Form::select('enterprise_generate', $generates, null , ['class' => 'select2 form-control' ,'name' => 'enterprise_generate', 'id' => 'enterprise_generate' ]) !!}
                  </div>
                  <label id="enterprise_generate-error" class="error d-none" for="available"></label>
                </div>
              </div>


              <div class="col-12">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Soporte</label>
                    <input type="text" class="form-control" id="supporting"  name="supporting"  placeholder="Ingresa el encargado de soporte">
                </div>
              </div>

              <div class="col-12">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Gerente</label>
                  <input type="text" class="form-control" id="leading"  name="leading"  placeholder="Ingresa el encargado de gerente">
                </div>
              </div>


              <div class="col-12">
                <div class="errors d-none">
                </div>
              </div>

              <div class="col-12">
                <div class="action-form border-top mt-4">
                  <div class="text-center">
                    <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
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
<script src="{{ asset('managers/js/views/distributors/distributors/create.js') }}"></script>
@endpush
