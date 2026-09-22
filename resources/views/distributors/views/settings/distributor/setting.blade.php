@extends('layouts.managers')


@section('page_header')
    @include('distributors.includes.card', ['title' => 'Visualizar distribuidor'])
@endsection
@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100 settings-distributor-card"
           data-update-url="{{ route('distributor.settings.distributor.update') }}"
           data-redirect-url="{{ route('distributor.dashboard') }}">



          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">
              <h5 class="mb-0">Visualizar distribuidor</h5>
            </div>
            <p class="card-subtitle mb-3 mt-3">
              Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos campos que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
            </p>

            <div class="row">

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Titulo</label>
                  <input type="text" class="form-control" id="title"  name="title"  value="{{ $distributor->title }}"   placeholder="Ingresa titulo" disabled>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Nit</label>
                  <input type="text" class="form-control" id="nit"  name="nit"  value="{{ $distributor->nit }}"  placeholder="Ingresa nit" disabled>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Celular</label>
                  <input type="text" class="form-control" id="cellphone"  name="cellphone" value="{{ $distributor->cellphone }}"   placeholder="Ingresa telefono" disabled>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Dirección</label>
                  <input type="text" class="form-control" id="address"  name="address" value="{{ $distributor->address }}"   placeholder="Ingresa dirección" disabled>
                </div>
              </div>
              <div class="col-12">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Correo electronico</label>
                  <input type="text" class="form-control" id="email"  name="email" value="{{ $distributor->email }}"  placeholder="Ingresa correo electronico" disabled>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Soporte</label>
                  <input type="text" class="form-control" id="supporting"  name="supporting"  value="{{ $distributor->supporting }}" placeholder="Ingresa el encargado de soporte" disabled>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label  class="control-label col-form-label">Gerente</label>
                  <input type="text" class="form-control" id="leading"  name="leading"  value="{{ $distributor->leading }}" placeholder="Ingresa el encargado de gerente" disabled>
                </div>
              </div>

          </div>

        </form>
      </div>

    </div>

  </div>

@endsection



@push('scripts')
    <script src="{{ asset('distributors/js/settings/distributor/setting.js') }}"></script>
@endpush
