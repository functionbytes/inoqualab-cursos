@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Editar empresa'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

      {{-- La empresa puede no tener distribuidor asociado (ver EnterpriseController::destroy). --}}
      {{-- Sin este fallback, $distributor->slack lanza un 500 al renderizar la vista. --}}
      <form id="formEnterprises" enctype="multipart/form-data" role="form" onSubmit="return false"
            data-update-url="{{ route('support.distributors.enterprises.update') }}"
            data-redirect-url="{{ ($distributor->slack ?? null) ? route('support.distributors.enterprises', $distributor->slack) : route('support.enterprises') }}">

        {{ csrf_field() }}

        <input type="hidden" id="slack" name="slack" value="{{ $enterprise->slack }}">

        <div class="card">

          <div class="card-body">
            <h6 class="fw-bold text-dark mb-1">Editar empresa</h6>
            <p class="text-muted mb-3">
              Actualiza los datos de la empresa. Los cambios se guardarán al hacer clic en Guardar.
            </p>

            <div class="row g-3">

              <div class="col-6">
                <label class="form-label fw-semibold">Titulo</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $enterprise->title }}" placeholder="Ingresa titulo" autocomplete="new-password">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Nit</label>
                <input type="text" class="form-control" id="nit" name="nit" value="{{ $enterprise->nit }}" placeholder="Ingresa nit" autocomplete="new-password">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Celular</label>
                <input type="text" class="form-control" id="cellphone" name="cellphone" value="{{ $enterprise->cellphone }}" placeholder="Ingresa telefono" autocomplete="new-password">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" class="form-control" id="address" name="address" value="{{ $enterprise->address }}" placeholder="Ingresa dirección" autocomplete="new-password">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Correo electronico</label>
                <input type="text" class="form-control" id="email" name="email" value="{{ $enterprise->email }}" placeholder="Ingresa correo electronico" autocomplete="new-password">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Estado</label>
                <div class="input-group">
                  {!! Form::select('available', $availables, $enterprise->available , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
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
          <p class="text-muted mb-0">Actualiza el <strong>Estado</strong> para habilitar o deshabilitar el acceso de la empresa al portal.</p>
        </div>
      </div>

    </div>

  </div>

@endsection



@push('scripts')
    <script src="{{ asset('supports/js/views/distributors/enterprises/edit.js') }}"></script>
@endpush
