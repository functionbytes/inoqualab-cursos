@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formEnterprises" enctype="multipart/form-data" role="form"
              data-store-url="{{ route('manager.enterprises.store') }}"
              data-redirect-url="{{ route('manager.enterprises') }}">

          {{ csrf_field() }}

          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">
              <h5 class="mb-0">Crear empresa</h5>
            </div>
            <p class="card-subtitle mb-3 mt-3">
              Completa los datos de la empresa. Los campos marcados como obligatorios deben diligenciarse para poder guardarla.
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
                    <label  class="control-label col-form-label">Dirección</label>
                    <input type="text" class="form-control" id="address"  name="address"  placeholder="Ingresa dirección">
                </div>
              </div>

              <div class="col-12">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Correo electronico</label>
                    <input type="text" class="form-control" id="email"  name="email"  placeholder="Ingresa correo electronico">
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
<script src="{{ asset('managers/js/views/enterprises/enterprises/create.js') }}"></script>
@endpush
