@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Visualizar distribuidor'])
@endsection
@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <div class="card-body border-top">
          <div class="d-flex no-block align-items-center">
            <h5 class="mb-0">Visualizar distribuidor</h5>
          </div>
          <p class="card-subtitle mb-3 mt-3">
            Informacion del distribuidor. Estos datos son de solo lectura, para modificarlos utiliza la opcion de editar.
          </p>

          <div class="row">

            <div class="col-6">
              <div class="mb-3">
                <label class="control-label col-form-label">Titulo</label>
                <input type="text" class="form-control" value="{{ $distributor->title }}" disabled>
              </div>
            </div>

            <div class="col-6">
              <div class="mb-3">
                <label class="control-label col-form-label">Nit</label>
                <input type="text" class="form-control" value="{{ $distributor->nit }}" disabled>
              </div>
            </div>

            <div class="col-6">
              <div class="mb-3">
                <label class="control-label col-form-label">Celular</label>
                <input type="text" class="form-control" value="{{ $distributor->cellphone }}" disabled>
              </div>
            </div>

            <div class="col-6">
              <div class="mb-3">
                <label class="control-label col-form-label">Dirección</label>
                <input type="text" class="form-control" value="{{ $distributor->address }}" disabled>
              </div>
            </div>

            <div class="col-6">
              <div class="mb-3">
                <label class="control-label col-form-label">Estado</label>
                <input type="text" class="form-control" value="{{ $distributor->available ? 'Publico' : 'Oculto' }}" disabled>
              </div>
            </div>

            <div class="col-6">
              <div class="mb-3">
                <label class="control-label col-form-label">Permisos empresa</label>
                <input type="text" class="form-control" value="{{ $distributor->enterprise_generate ? 'Si' : 'No' }}" disabled>
              </div>
            </div>

            <div class="col-12">
              <div class="mb-3">
                <label class="control-label col-form-label">Correo electronico</label>
                <input type="text" class="form-control" value="{{ $distributor->email }}" disabled>
              </div>
            </div>

            <div class="col-12">
              <div class="mb-3">
                <label class="control-label col-form-label">Soporte</label>
                <input type="text" class="form-control" value="{{ $distributor->supporting }}" disabled>
              </div>
            </div>

            <div class="col-12">
              <div class="mb-3">
                <label class="control-label col-form-label">Gerente</label>
                <input type="text" class="form-control" value="{{ $distributor->leading }}" disabled>
              </div>
            </div>

          </div>
        </div>

      </div>

    </div>

  </div>

@endsection
