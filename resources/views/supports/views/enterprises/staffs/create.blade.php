@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Crear empleado'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

      <form id="formUsers" enctype="multipart/form-data" role="form"
            data-store-url="{{ route('support.enterprises.staffs.store') }}"
            data-redirect-url="{{ route('support.enterprises.staffs', ':slack') }}">

        {{ csrf_field() }}

        <input type="hidden" id="id" name="id" value="">
        <input type="hidden" id="slack" name="slack" value="">
        <input type="hidden" id="edit" name="edit" value="true">
        <input type="hidden" id="enterprise" name="enterprise" value="{{ $enterprise->slack }}">

        <div class="card">

          <div class="card-header border-bottom">
              <h6 class="mb-1 fw-bold">Crear empleado</h6>
              <p class="text-muted small mb-0">
                  Completa los datos del empleado. Los campos marcados como obligatorios deben diligenciarse para poder guardarlo.
              </p>
          </div>

          <div class="card-body">
            <div class="row g-3">

              <div class="col-6">
                <label class="form-label fw-semibold">Nombres</label>
                <input type="text" class="form-control" id="firstname" name="firstname" value="" placeholder="Ingresar nombres" autocomplete="new-password">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Apellidos</label>
                <input type="text" class="form-control" id="lastname" name="lastname" value="" placeholder="Ingresar apellido" autocomplete="new-password">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Identificación</label>
                <input type="text" class="form-control" id="identification" name="identification" value="" placeholder="Ingresar identificación" autocomplete="new-password">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Correo electronico</label>
                <input type="text" class="form-control" id="email" name="email" value="" placeholder="Ingresar correo electronico" autocomplete="new-password">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" class="form-control" id="address" name="address" value="" placeholder="Ingresar dirección" autocomplete="new-password">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Celular</label>
                <input type="text" class="form-control" id="cellphone" name="cellphone" value="" placeholder="Ingresar celular" autocomplete="new-password">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" value="" placeholder="Ingresar contraseña" autocomplete="new-password">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Estado</label>
                <div class="input-group">
                  {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                </div>
                <label id="available-error" class="error d-none" for="available"></label>
              </div>

              <div class="col-12">
                <div class="errors d-none"></div>
              </div>

            </div>
          </div>

          <div class="card-footer">
            <button type="submit" class="btn btn-primary w-100">
              Guardar
            </button>
          </div>

        </div>
      </form>
    </div>

    {{-- Columna derecha: sidebar informativo --}}
    <div class="col-lg-4">

      <div class="card">
        <div class="card-header border-bottom">
          <h6 class="mb-0 fw-bold">Sobre los empleados</h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-0">Los empleados de la empresa tienen acceso a su portal para gestionar las inscripciones y cursos de sus usuarios asignados.</p>
        </div>
      </div>

    </div>

  </div>

@endsection



@push('scripts')
    <script src="{{ asset('supports/js/enterprises/staffs/create.js') }}"></script>
@endpush
