@extends('layouts.managers')


@section('page_header')
    @include('enterprises.includes.card', ['title' => 'Mi perfil'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

      <form id="formUsers" enctype="multipart/form-data" role="form" onSubmit="return false" data-update-url="{{ route('enterprise.profile.update') }}">

        {{ csrf_field() }}

        <input type="hidden" id="id" name="id" value="{{ $user->id }}">
        <input type="hidden" id="slack" name="slack" value="{{ $user->slack }}">

        <div class="card">

          <div class="card-body">
            <h6 class="fw-bold text-dark mb-1">Mi perfil</h6>
            <p class="text-muted mb-3">
              Actualiza tus datos personales. Los cambios se guardarán al hacer clic en Guardar.
            </p>

            <div class="row g-3">

              <div class="col-6">
                <label class="form-label fw-semibold">Nombres</label>
                <input type="text" class="form-control" id="firstname" name="firstname" value="{{ $user->firstname }}" placeholder="Ingresar nombres">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Apellidos</label>
                <input type="text" class="form-control" id="lastname" name="lastname" value="{{ $user->lastname }}" placeholder="Ingresar apellido">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Identificación</label>
                <input type="text" class="form-control" id="identification" name="identification" value="{{ $user->identification }}" placeholder="Ingresar identificación">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Correo electrónico</label>
                <input type="text" class="form-control" id="email" value="{{ $user->email }}" readonly>
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" class="form-control" id="address" name="address" value="{{ $user->address }}" placeholder="Ingresar dirección">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" value="" placeholder="Ingresar contraseña">
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
          <h6 class="mb-0 fw-bold">Sobre tu perfil</h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-0">El correo electrónico es tu usuario de acceso y no se puede modificar. Deja la contraseña en blanco si no deseas cambiarla.</p>
        </div>
      </div>

    </div>

  </div>

@endsection

@push('scripts')
  <script src="{{ asset('enterprises/js/views/settings/edit.js') }}"></script>
@endpush
