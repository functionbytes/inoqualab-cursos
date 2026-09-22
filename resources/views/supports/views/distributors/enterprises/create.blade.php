@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Crear empresass'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

      <form id="formEnterprises" enctype="multipart/form-data" role="form" onSubmit="return false"
            data-store-url="{{ route('support.distributors.enterprises.store') }}"
            data-redirect-url-template="{{ route('support.distributors.enterprises', ':slack') }}">

        {{ csrf_field() }}

        <input type="hidden" id="distributor" name="distributor" value="{{ $distributor->slack }}">

        <div class="card">

          <div class="card-body">
            <h6 class="fw-bold text-dark mb-1">Crear empresa</h6>
            <p class="text-muted mb-3">
              Completa los datos de la empresa. Los campos marcados como obligatorios deben diligenciarse para poder guardarla.
            </p>

            <div class="row g-3">

              <div class="col-6">
                <label class="form-label fw-semibold">Titulo</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="Ingresa titulo" autocomplete="new-password">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Nit</label>
                <input type="text" class="form-control" id="nit" name="nit" placeholder="Ingresa nit" autocomplete="new-password">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Celular</label>
                <input type="text" class="form-control" id="cellphone" name="cellphone" placeholder="Ingresa telefono" autocomplete="new-password">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" class="form-control" id="address" name="address" placeholder="Ingresa dirección" autocomplete="new-password">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Correo electronico</label>
                <input type="text" class="form-control" id="email" name="email" placeholder="Ingresa correo electronico" autocomplete="new-password">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Estado</label>
                <div class="input-group">
                  {!! Form::select('available', $availables, null , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
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
          <h6 class="mb-0 fw-bold">Sobre las empresas</h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-0">La empresa queda asociada al distribuidor actual. Podrás asignarle usuarios y cursos una vez creada.</p>
        </div>
      </div>

    </div>

  </div>

@endsection



@push('scripts')
    <script src="{{ asset('supports/js/views/distributors/enterprises/create.js') }}"></script>
@endpush
