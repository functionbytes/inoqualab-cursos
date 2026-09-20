@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100">

        <form id="formTrusted" enctype="multipart/form-data" role="form"
              data-urls='@php $__jsonInline1 = [
                  "store" => route("manager.trusteds.store"),
                  "index" => route("manager.trusteds"),
                  "thumbnails" => route("manager.trusteds.thumbnails"),
                  "thumbnailDelete" => route("manager.trusteds.thumbnails.delete", ":id"),
              ]; @endphp@json($__jsonInline1)'>

          {{ csrf_field() }}

          <input type="hidden" id="id" name="id" value="">
          <input type="hidden" id="slack" name="slack" value="">
          <input type="hidden" id="status" name="status" value="false">
          <input type="hidden" id="edit" name="edit" value="true">
          <input type="hidden" id="thumbnail" name="thumbnail">

          <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Imagen</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Sube el logo de la empresa aliada.
                        </p>
                        <div class="dropzone dz-clickable" id="thumbnail">
                            <div class="fallback">
                                <input type="file" hidden name="file">
                            </div>
                        </div>
                        <label id="thumbnail-error" class="error d-none" for="thumbnail"></label>
                    </div>

          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">
              <h5 class="mb-0">Crear aliado</h5>
            </div>
            <p class="card-subtitle mb-3 mt-3">
              Completa los datos de la empresa aliada. Los campos marcados como obligatorios deben diligenciarse para poder guardarla.
            </p>
            <div class="row">

              <div class="col-6">
                <div class="mb-3">
                  <div class="mb-3">
                    <label  class="control-label col-form-label">Titulo</label>
                    <input type="text" class="form-control" id="title"  name="title" value="" placeholder="Ingresar titulo">
                  </div>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Estado</label>
                  <div class="input-group">
                    {!! Form::select('available', $availables, null , ['class' => 'select2 form-control','id' => 'available']) !!}
                  </div>
                  <label id="available-error" class="error d-none" for="available"></label>
                </div>
              </div>

              <div class="col-12">
                <div class="mb-3">
                  <div class="mb-3">
                    <label  class="control-label col-form-label">Link</label>
                    <input type="text" class="form-control" id="url"  name="url" value="" placeholder="Ingresar link">
                  </div>
                </div>
              </div>
                <div class="col-12">
                    <div class="action-form border-top mt-4">
                        <div class="text-center">
                            <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                Guardar
                            </button>
                        </div>
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
<script src="{{ asset('managers/js/views/settings/trusteds/create.js') }}"></script>
@endpush
