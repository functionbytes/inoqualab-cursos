@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formEnterprises" enctype="multipart/form-data" role="form" onSubmit="return false"
              data-update-url="{{ route('distributor.enterprises.update') }}"
              data-redirect-url="{{ route('distributor.enterprises') }}">

          {{ csrf_field() }}

          <input type="hidden" id="slack" name="slack" value="{{ $enterprise->slack }}">


          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">
              <h5 class="mb-0">Editar empresa</h5>
            </div>
            <p class="card-subtitle mb-3 mt-3">
              Este espacio está diseñado para que puedas actualizar y modificar la información de manera eficiente y segura. A continuación, encontrarás diversos campos que corresponden a los datos previamente suministrados. Te invitamos a revisar y ajustar cualquier información que consideres necesario actualizar para mantener tus datos al día.
            </p>

            <div class="row">
              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Titulo</label>
                    <input type="text" class="form-control" id="title"  name="title"  value="{{ $enterprise->title }}"   placeholder="Ingresa titulo">
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Nit</label>
                    <input type="text" class="form-control" id="nit"  name="nit"  value="{{ $enterprise->nit }}"  placeholder="Ingresa nit">
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Celular</label>
                    <input type="text" class="form-control" id="cellphone"  name="cellphone" value="{{ $enterprise->cellphone }}"   placeholder="Ingresa telefono">
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Dirección</label>
                    <input type="text" class="form-control" id="address"  name="address" value="{{ $enterprise->address }}"   placeholder="Ingresa dirección">
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                    <label  class="control-label col-form-label">Correo electronico</label>
                    <input type="text" class="form-control" id="email"  name="email" value="{{ $enterprise->email }}"  placeholder="Ingresa correo electronico">
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Estado</label>
                  <div class="input-group">
                    {!! Form::select('available', $availables, $enterprise->available , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
                  </div>
                  <label id="available-error" class="error d-none" for="available"></label>
                </div>
              </div>

              <div class="col-12">
                <div class="errors d-none">
                </div>
              </div>
              <div class="col-12">
                  <div class="border-top pt-1 mt-4">
                        <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                        Guardar
                        </button>
                  </div>
              </div>

            </div>
          </div>

        </form>
      </div>

    </div>

  </div>

@endsection



@push('scripts')
    <script src="{{ asset('distributors/js/enterprises/enterprises/edit.js') }}"></script>
@endpush
