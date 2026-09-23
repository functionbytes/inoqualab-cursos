@extends('layouts.managers')


@section('page_header')
    @include('accountings.includes.card', ['title' => 'Visualizar empresa'])
@endsection
@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formEnterprises" enctype="multipart/form-data" role="form" onSubmit="return false">

          {{ csrf_field() }}

          <input type="hidden" id="slack" name="slack" value="{{ $enterprise->slack }}">


          <div class="card-header border-bottom">
            <h6 class="mb-1 fw-bold">Visualizar empresa</h6>
            <p class="text-muted small mb-0">
              Información de contacto de esta empresa. Estos datos son de solo lectura desde
              esta pantalla.
            </p>
          </div>

          <div class="card-body">
            <div class="row g-3">
              <div class="col-6">
                <label class="form-label fw-semibold">Titulo</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $enterprise->title }}" placeholder="Ingresa titulo" disabled>
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Nit</label>
                <input type="text" class="form-control" id="nit" name="nit" value="{{ $enterprise->nit }}" placeholder="Ingresa nit" disabled>
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Celular</label>
                <input type="text" class="form-control" id="cellphone" name="cellphone" value="{{ $enterprise->cellphone }}" placeholder="Ingresa telefono" disabled>
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Dirección</label>
                <input type="text" class="form-control" id="address" name="address" value="{{ $enterprise->address }}" placeholder="Ingresa dirección" disabled>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Correo electronico</label>
                <input type="text" class="form-control" id="email" name="email" value="{{ $enterprise->email }}" placeholder="Ingresa correo electronico" disabled>
              </div>

            </div>

          </div>

        </form>
      </div>

    </div>

  </div>

@endsection


