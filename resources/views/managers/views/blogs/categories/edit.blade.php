@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar categoria'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

      <div id="blogs-categories-edit"
           data-config='@php $__jsonInline1 = [
              "routes" => [
                  "update" => route("manager.blogs.categories.update"),
                  "index" => route("manager.blogs.categories"),
              ],
           ]; @endphp@json($__jsonInline1)'>

        <form id="formCategories" enctype="multipart/form-data" role="form">

          {{ csrf_field() }}

          <input type="hidden" id="slack" name="slack" value="{{ $categorie->slack }}">

          <div class="card">

          <div class="card-body">
            <h6 class="fw-bold text-dark mb-1">Editar categoria</h6>
            <p class="text-muted mb-3">
              Actualiza los datos de la categoría de blog. Los cambios se guardarán al hacer clic en Guardar.
            </p>

            <div class="row g-3">
              <div class="col-6">
                <label class="form-label fw-semibold">Titulo</label>
                <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ $categorie->title  }}" >
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold">Estado</label>
                <div class="input-group">
                  {!! Form::select('available', $availables, $categorie->available , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
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
    </div>

    {{-- Columna derecha: sidebar informativo --}}
    <div class="col-lg-4">

      <div class="card">
        <div class="card-header border-bottom">
          <h6 class="mb-0 fw-bold">Sobre las categorías</h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-0">Agrupan las noticias del blog. Solo las categorías <strong>activas</strong> se muestran como filtro.</p>
        </div>
      </div>

    </div>

  </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/blogs/categories/edit.js') }}"></script>
@endpush


