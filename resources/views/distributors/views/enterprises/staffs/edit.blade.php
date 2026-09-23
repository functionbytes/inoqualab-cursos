@extends('layouts.managers')


@section('page_header')
    @include('distributors.includes.card', ['title' => 'Editar empleado'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

      <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false"
            data-update-url="{{ route('distributor.enterprises.staffs.update') }}"
            data-redirect-url="{{ route('distributor.enterprises.staffs', $distributor->slack) }}">

        {{ csrf_field() }}

        <input type="hidden" id="id" name="id" value="{{ $user->id }}">
        <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">
        <input type="hidden" id="distributor" name="enterprise" value="{{ $distributor->slack }}">
        <input type="hidden" id="edit" name="edit" value="true">

        <div class="card">

          <div class="card-header border-bottom">
              <h6 class="mb-1 fw-bold">Editar empleado</h6>
              <p class="text-muted small mb-0">
                  Actualiza los datos del empleado. Los cambios se guardarán al hacer clic en Guardar.
              </p>
          </div>

          <div class="card-body">
            <div class="row g-3">

              <div class="col-6">
                <label class="form-label fw-semibold">Nombres</label>
                <input type="text" class="form-control" id="firstname"  name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Apellidos</label>
                <input type="text" class="form-control" id="lastname"  name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Identificación</label>
                <input type="text" class="form-control" id="identification"  name="identification" value="{{ $user->identification }}" placeholder="Ingresar identificación">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Correo electronico</label>
                <input type="text" class="form-control" id="email"  name="email" value="{{ $user->email }}" placeholder="Ingresar correo electronico">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" class="form-control" id="address"  name="address" value="{{ $user->address }}" placeholder="Ingresar dirección">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Celular</label>
                <input type="text" class="form-control" id="cellphone"  name="cellphone" value="{{ $user->cellphone }}" placeholder="Ingresar celular">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Contraseña</label>
                <input type="password" class="form-control" id="password"  name="password" value="" placeholder="Ingresar contraseña">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Estado</label>
                <div class="input-group">
                  {!! Form::select('available', $availables, $user->available , ['class' => 'select2 form-control','id' => 'available']) !!}
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
          <p class="text-muted mb-0">Solo los empleados con estado <strong>activo</strong> pueden acceder al portal de la empresa.</p>
        </div>
      </div>

    </div>

  </div>

@endsection



@push('scripts')
    <script src="{{ asset('distributors/js/enterprises/staffs/edit.js') }}"></script>
@endpush



