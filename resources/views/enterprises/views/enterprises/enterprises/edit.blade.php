@extends('layouts.managers')


@section('page_header')
    @include('enterprises.includes.card', ['title' => 'Editar empresa'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

      <form id="formEnterprises" enctype="multipart/form-data" role="form" onSubmit="return false"
            data-update-url="{{ route('enterprise.enterprise.update') }}"
            data-redirect-url="{{ route('enterprise.dashboard') }}">

        {{ csrf_field() }}

        <input type="hidden" id="slack" name="slack" value="{{ $enterprise->slack }}">

        <div class="card">

          <div class="card-header border-bottom">
              <h6 class="mb-1 fw-bold">Editar empresa</h6>
              <p class="text-muted small mb-0">
                  Actualiza los datos de contacto de la empresa. El título y el NIT son gestionados por el distribuidor y no se pueden modificar aquí.
              </p>
          </div>

          <div class="card-body">
            <div class="row g-3">

              <div class="col-6">
                <label class="form-label fw-semibold">Titulo</label>
                <input type="text" class="form-control" id="title"  name="title"  value="{{ $enterprise->title }}"   placeholder="Ingresa titulo" disabled>
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Nit</label>
                <input type="text" class="form-control" id="nit"  name="nit"  value="{{ $enterprise->nit }}"  placeholder="Ingresa nit" disabled>
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Celular</label>
                <input type="text" class="form-control" id="cellphone"  name="cellphone" value="{{ $enterprise->cellphone }}"   placeholder="Ingresa telefono">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" class="form-control" id="address"  name="address" value="{{ $enterprise->address }}"   placeholder="Ingresa dirección">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Correo electronico</label>
                <input type="text" class="form-control" id="email"  name="email" value="{{ $enterprise->email }}"   placeholder="Ingresa correo electronico">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Encargado</label>
                <input type="text" class="form-control" id="supporting" name="supporting" value="{{ $enterprise->supporting }}" placeholder="Ingresa un encargado">
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
          <h6 class="mb-0 fw-bold">Sobre tu empresa</h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-0">El nombre de contacto que registres en <strong>Encargado</strong> es quien recibirá las comunicaciones relacionadas con la empresa.</p>
        </div>
      </div>

    </div>

  </div>

@endsection



@push('scripts')
  <script src="{{ asset('enterprises/js/views/enterprises/enterprises/edit.js') }}"></script>
@endpush
