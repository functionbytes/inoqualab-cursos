@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formUsers" enctype="multipart/form-data" role="form"
              data-store-url="{{ route('manager.distributors.staffs.store') }}"
              data-navegation-url="{{ route('manager.distributors.staffs', ':slack') }}"
              data-distributor-slack="{{ $distributor->slack }}">

          {{ csrf_field() }}

          <input type="hidden" id="id" name="id" value="">
          <input type="hidden" id="slack" name="slack" value="">
          <input type="hidden" id="edit" name="edit" value="true">
          <input type="hidden" id="distributor" name="distributor" value="{{ $distributor->slack }}">

          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">

              <h5 class="mb-0">Crear empleado
              </h5>

            </div>
            <p class="card-subtitle mb-3 mt-3">
              Completa los datos del empleado. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
            </p>

            <div class="row">

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Nombres</label>
                    <input type="text" class="form-control" id="firstname"  name="firstname" value="" placeholder="Ingresar nombres">
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Apellidos</label>
                    <input type="text" class="form-control" id="lastname"  name="lastname" value="" placeholder="Ingresar apellido">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Identificación</label>
                    <input type="text" class="form-control" id="identification"  name="identification" value="" placeholder="Ingresar identificación">
                </div>
              </div>


              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Dirección</label>
                    <input type="text" class="form-control" id="address"  name="address" value="" placeholder="Ingresar dirección">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Celular</label>
                    <input type="text" class="form-control" id="cellphone"  name="cellphone" value="" placeholder="Ingresar ce">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password"  name="password" value="" placeholder="Ingresar contraseña" autocomplete="new-password">
                </div>
              </div>

                <div class="col-12">
                    <div class="mb-3">
                        <label  class="control-label col-form-label">Correo electronico</label>
                        <input type="text" class="form-control" id="email"  name="email" value="" placeholder="Ingresar correo electronico" v>
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
<script src="{{ asset('managers/js/views/distributors/staffs/create.js') }}"></script>
@endpush
