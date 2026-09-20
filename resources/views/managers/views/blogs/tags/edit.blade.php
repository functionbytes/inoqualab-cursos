@extends('layouts.managers')

@section('content')

  <div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

      <div class="card w-100" id="blogs-tags-edit"
           data-config='@php $__jsonInline1 = [
              "routes" => [
                  "update" => route("manager.blogs.tags.update"),
                  "index" => route("manager.blogs.tags"),
              ],
           ]; @endphp@json($__jsonInline1)'>

        <form id="formTags" enctype="multipart/form-data" role="form">

          {{ csrf_field() }}

          <input type="hidden" id="slack" name="slack" value="{{ $tag->slack }}">

          <div class="card-body border-top">
            <div class="d-flex no-block align-items-center">
              <h5 class="mb-0">Editar etiqueta</h5>
            </div>
            <p class="card-subtitle mb-3 mt-3">
              Actualiza los datos de la etiqueta. Los cambios se guardarán al hacer clic en Guardar.
            </p>

            <div class="row">
              <div class="col-6">
                <div class="mb-3">
                  <div class="mb-3">
                    <label  class="control-label col-form-label">Titulo</label>
                    <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ $tag->title  }}" >
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                  <label class="control-label col-form-label">Estado</label>
                  <div class="input-group">
                    {!! Form::select('available', $availables, $tag->available , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
                  </div>
                </div>
              </div><div class="col-12">
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
<script src="{{ asset('managers/js/views/blogs/tags/edit.js') }}"></script>
@endpush



