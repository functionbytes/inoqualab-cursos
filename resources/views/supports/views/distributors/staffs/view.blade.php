@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Visualizar empleado'])
@endsection
@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false">

          {{ csrf_field() }}

          <input type="hidden" id="id" name="id" value="{{ $user->id }}">
          <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">
          <input type="hidden" id="edit" name="edit" value="true">

          <div class="card-header border-bottom">
            <h6 class="mb-1 fw-bold">Visualizar empleado</h6>
            <p class="text-muted small mb-0">
              Información de contacto de este empleado del distribuidor. Estos datos son de solo
              lectura desde esta pantalla.
            </p>
          </div>

          <div class="card-body">
            <div class="row g-3">

              <div class="col-6">
                <label class="form-label fw-semibold">Nombres</label>
                <input type="text" class="form-control" disabled id="firstname" name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Apellidos</label>
                <input type="text" class="form-control" disabled id="lastname" name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Identificación</label>
                <input type="text" class="form-control" disabled id="identification" name="identification" value="{{ $user->identification }}" placeholder="Ingresar identificación">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Celular</label>
                <input type="text" class="form-control" disabled id="cellphone" name="cellphone" value="{{ $user->cellphone }}" placeholder="Ingresar celular">
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold">Correo electronico</label>
                <input type="text" class="form-control" disabled id="email" name="email" value="{{ $user->email }}" placeholder="Ingresar correo electronico">
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" class="form-control" disabled id="address" name="address" value="{{ $user->address }}" placeholder="Ingresar dirección">
              </div>

              <div class="col-12">
                <div class="errors d-none"></div>
              </div>

            </div>
          </div>

        </form>
      </div>

    </div>

  </div>

@endsection



@push('scripts')

@endpush



