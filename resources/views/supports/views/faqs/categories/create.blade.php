@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formCategories" enctype="multipart/form-data" role="form"
              data-store-url="{{ route('support.faqs.categories.store') }}"
              data-redirect-url="{{ route('support.faqs.categories') }}">

          {{ csrf_field() }}

              <div class="card-body border-top">
                <div class="d-flex no-block align-items-center">
                  <h5 class="mb-0">Crear categoria</h5>
                </div>
                <p class="card-subtitle mb-3 mt-3">
                  Este espacio está diseñado para permitirte  introducir nueva información de manera sencilla y estructurada. A continuación, se presentan varios campos que deberás completar con los datos requeridos.
                </p>
                <div class="row">

                  <div class="col-6">
                    <div class="mb-3">
                        <label  class="control-label col-form-label">Titulo</label>
                        <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo">
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="mb-3">
                      <label class="control-label col-form-label">Estado</label>
                      <div class="input-group">
                        {!! Form::select('available', $availables, null , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
                      </div>
                    <label id="available-error" class="error d-none" for="available"></label>
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
<script src="{{ asset('supports/js/views/faqs/categories/create.js') }}"></script>
@endpush

