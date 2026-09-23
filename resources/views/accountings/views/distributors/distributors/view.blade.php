@extends('layouts.managers')


@section('page_header')
    @include('accountings.includes.card', ['title' => 'Visualizar distribuidor'])
@endsection
@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">


          <div class="card-header border-bottom">
            <h6 class="mb-1 fw-bold">Visualizar distribuidor</h6>
            <p class="text-muted small mb-0">
              Información de contacto de este distribuidor. Estos datos son de solo lectura desde
              esta pantalla.
            </p>
          </div>

          <div class="card-body">
            <div class="row g-3">

              <div class="col-6">
                <label class="form-label fw-semibold">Titulo</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $distributor->title }}" placeholder="Ingresa titulo" disabled>
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Nit</label>
                <input type="text" class="form-control" id="nit" name="nit" value="{{ $distributor->nit }}" placeholder="Ingresa nit" disabled>
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Celular</label>
                <input type="text" class="form-control" id="cellphone" name="cellphone" value="{{ $distributor->cellphone }}" placeholder="Ingresa telefono" disabled>
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" class="form-control" id="address" name="address" value="{{ $distributor->address }}" placeholder="Ingresa dirección" disabled>
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Correo electronico</label>
                <input type="text" class="form-control" id="email" name="email" value="{{ $distributor->email }}" placeholder="Ingresa correo electronico" disabled>
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Estado</label>
                {!! Form::select('available', $availables, $distributor->available , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available', 'disabled' => 'disabled'  ]) !!}
                <label id="available-error" class="error d-none" for="available"></label>
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold">Soporte</label>
                <input type="text" class="form-control" id="supporting" name="supporting" value="{{ $distributor->supporting }}" placeholder="Ingresa el encargado de soporte" disabled>
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold">Gerente</label>
                <input type="text" class="form-control" id="leading" name="leading" value="{{ $distributor->leading }}" placeholder="Ingresa el encargado de gerente" disabled>
              </div>

            </div>
          </div>

      </div>

    </div>

  </div>

@endsection
