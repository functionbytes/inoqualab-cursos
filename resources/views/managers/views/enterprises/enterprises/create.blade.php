@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Crear empresa'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

      <form id="formEnterprises" enctype="multipart/form-data" role="form"
            data-store-url="{{ route('manager.enterprises.store') }}"
            data-redirect-url="{{ route('manager.enterprises') }}">

        {{ csrf_field() }}

        <div class="card">

          <div class="card-header border-bottom">
              <h6 class="mb-1 fw-bold">Crear empresa</h6>
              <p class="text-muted small mb-0">
                  Completa los datos de la empresa. Los campos marcados como obligatorios deben diligenciarse para poder guardarla.
              </p>
          </div>

          <div class="card-body">
            <div class="row g-3">

              <div class="col-6">
                <label class="form-label fw-semibold">Titulo</label>
                <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Nit</label>
                <input type="text" class="form-control" id="nit"  name="nit"  placeholder="Ingresa nit">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Celular</label>
                <input type="text" class="form-control" id="cellphone"  name="cellphone"  placeholder="Ingresa telefono">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" class="form-control" id="address"  name="address"  placeholder="Ingresa dirección">
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold">Correo electronico</label>
                <input type="text" class="form-control" id="email"  name="email"  placeholder="Ingresa correo electronico">
              </div>

              <div class="col-12">
                <div class="errors d-none">
                </div>
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
          <h6 class="mb-0 fw-bold">Sobre las empresas</h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-0">Una vez creada, podrás asignarle usuarios, cursos y tarifas desde su panel de navegación.</p>
        </div>
      </div>

    </div>

  </div>


@endsection


@push('scripts')
<script src="{{ asset('managers/js/views/enterprises/enterprises/create.js') }}"></script>
@endpush
