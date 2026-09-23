@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Visualizar distribuidor'])
@endsection
@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <div class="card-header border-bottom">
          <h6 class="mb-1 fw-bold">Visualizar distribuidor</h6>
          <p class="text-muted small mb-0">
            Informacion del distribuidor. Estos datos son de solo lectura, para modificarlos utiliza la opcion de editar.
          </p>
        </div>

        <div class="card-body">
          <div class="row g-3">

            <div class="col-6">
              <label class="form-label fw-semibold">Titulo</label>
              <input type="text" class="form-control" value="{{ $distributor->title }}" disabled>
            </div>

            <div class="col-6">
              <label class="form-label fw-semibold">Nit</label>
              <input type="text" class="form-control" value="{{ $distributor->nit }}" disabled>
            </div>

            <div class="col-6">
              <label class="form-label fw-semibold">Celular</label>
              <input type="text" class="form-control" value="{{ $distributor->cellphone }}" disabled>
            </div>

            <div class="col-6">
              <label class="form-label fw-semibold">Dirección</label>
              <input type="text" class="form-control" value="{{ $distributor->address }}" disabled>
            </div>

            <div class="col-6">
              <label class="form-label fw-semibold">Estado</label>
              <input type="text" class="form-control" value="{{ $distributor->available ? 'Publico' : 'Oculto' }}" disabled>
            </div>

            <div class="col-6">
              <label class="form-label fw-semibold">Permisos empresa</label>
              <input type="text" class="form-control" value="{{ $distributor->enterprise_generate ? 'Si' : 'No' }}" disabled>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Correo electronico</label>
              <input type="text" class="form-control" value="{{ $distributor->email }}" disabled>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Soporte</label>
              <input type="text" class="form-control" value="{{ $distributor->supporting }}" disabled>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Gerente</label>
              <input type="text" class="form-control" value="{{ $distributor->leading }}" disabled>
            </div>

          </div>
        </div>

      </div>

    </div>

  </div>

@endsection
