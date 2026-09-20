@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formUsers" enctype="multipart/form-data" role="form"
              data-store-url="{{ route('manager.enterprises.users.store') }}"
              data-redirect-url="{{ route('manager.enterprises.users', $enterprise->slack) }}">

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
              Completa los datos del nuevo usuario de la empresa. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
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

              <div class="col-12">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Correo electronico</label>
                  <input type="text" class="form-control" id="email"  name="email" value="" placeholder="Ingresar correo electronico" autocomplete="new-password" >
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
                  <label  class="control-label col-form-label">Identificación</label>
                  <input type="text" class="form-control" id="identification"  name="identification" value="" placeholder="Ingresar identificación">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Celular</label>
                  <input type="text" class="form-control" id="cellphone"  name="cellphone" value="" placeholder="Ingresar celular">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <div class="mb-3">
                    <label  class="control-label col-form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password"  name="password" value="" placeholder="Ingresar contraseña" autocomplete="new-password">
                  </div>
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

  <div id="course-modal" class="modal fade">
    <div class="modal-dialog modal-md modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
        </div>
        <div class="modal-body text-center">
          <div class="display-4 text-danger"><i data-feather="x-octagon"></i></div>
          <h4 class="my-0">¿Deseas asignar este usuario a un curso?</h4>
          <p>Visualizaras los cursos de la empresa</p>
          <div class="row justify-content-center mt-20  ">
            <div class="col-sm-12 col-md-5">
              <a href="" id="course-link" class="btn btn-danger w-100">Confirmar</a>
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
<script src="{{ asset('managers/js/views/enterprises/users/create.js') }}"></script>
@endpush



