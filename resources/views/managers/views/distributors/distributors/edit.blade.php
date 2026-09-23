@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar distribuidor'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

      <form id="formDistributors" enctype="multipart/form-data" role="form"
            data-update-url="{{ route('manager.distributors.update') }}"
            data-redirect-url="{{ route('manager.distributors') }}">

        {{ csrf_field() }}

        <input type="hidden" id="slack" name="slack" value="{{ $distributor->slack }}">

        <div class="card">

          <div class="card-header border-bottom">
              <h6 class="mb-1 fw-bold">Editar distribuidor</h6>
              <p class="text-muted small mb-0">
                  Actualiza los datos del distribuidor. Los cambios se guardarán al hacer clic en Guardar.
              </p>
          </div>

          <div class="card-body">
            <div class="row g-3">

              <div class="col-6">
                <label class="form-label fw-semibold">Titulo</label>
                <input type="text" class="form-control" id="title"  name="title"  value="{{ $distributor->title }}"   placeholder="Ingresa titulo">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Nit</label>
                <input type="text" class="form-control" id="nit"  name="nit"  value="{{ $distributor->nit }}"  placeholder="Ingresa nit">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Celular</label>
                <input type="text" class="form-control" id="cellphone"  name="cellphone" value="{{ $distributor->cellphone }}"   placeholder="Ingresa telefono">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" class="form-control" id="address"  name="address" value="{{ $distributor->address }}"   placeholder="Ingresa dirección">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Estado</label>
                <div class="input-group">
                  {!! Form::select('available', $availables, $distributor->available , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
                </div>
                <label id="available-error" class="error d-none" for="available"></label>
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Permisos empresa</label>
                <div class="input-group">
                    {!! Form::select('enterprise_generate', $generates, $distributor->enterprise_generate , ['class' => 'select2 form-control' ,'name' => 'enterprise_generate', 'id' => 'enterprise_generate' ]) !!}
                </div>
                <label id="enterprise_generate-error" class="error d-none" for="available"></label>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Correo electronico</label>
                <input type="text" class="form-control" id="email"  name="email" value="{{ $distributor->email }}"  placeholder="Ingresa correo electronico">
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold">Soporte</label>
                <input type="text" class="form-control" id="supporting"  name="supporting"  value="{{ $distributor->supporting }}" placeholder="Ingresa el encargado de soporte">
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold">Gerente</label>
                <input type="text" class="form-control" id="leading"  name="leading"  value="{{ $distributor->leading }}" placeholder="Ingresa el encargado de gerente">
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
          <h6 class="mb-0 fw-bold">Sobre los distribuidores</h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-0"><strong>Permisos empresa</strong> controla si este distribuidor puede generar sus propias empresas clientes desde su portal.</p>
        </div>
      </div>

    </div>

  </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/distributors/distributors/edit.js') }}"></script>
@endpush
