@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar categoria'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

      <form id="formCategories" enctype="multipart/form-data" role="form"
            data-urls='@php $__jsonInline1 = [
                "update" => route("manager.instructions.categories.update"),
                "index" => route("manager.instructions.categories"),
            ]; @endphp@json($__jsonInline1)'>

        {{ csrf_field() }}

        <input type="hidden" id="id" name="id" value="{{ $categorie->id }}">
        <input type="hidden" id="slack" name="slack" value="{{ $categorie->slack }}">

        <div class="card">

          <div class="card-header border-bottom">
              <h6 class="mb-1 fw-bold">Editar categoria</h6>
              <p class="text-muted small mb-0">
                  Actualiza los datos de la categoria de instrucciones. Los cambios se guardarán al hacer clic en Guardar.
              </p>
          </div>

          <div class="card-body">
            <div class="row g-3">

              <div class="col-12">
                <label class="form-label fw-semibold">Titulo</label>
                <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ $categorie->title  }}" >
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Icono</label>
                <input type="text" class="form-control" id="icon"  name="icon"  placeholder="Ingresa el icono" value=" {{ $categorie->icon  }}">
                <small class="text-muted d-block mt-1">Clase de Font Awesome 6, ej: <code>fas fa-book</code></small>
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Estado</label>
                <div class="input-group">
                  {!! Form::select('available', $availables, $categorie->available , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
                </div>
                <label id="available-error" class="error d-none" for="available"></label>
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

    {{-- Columna derecha: sidebar informativo --}}
    <div class="col-lg-4">

      <div class="card">
        <div class="card-header border-bottom">
          <h6 class="mb-0 fw-bold">Sobre las categorías</h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-0">Agrupan las guías e instrucciones del sitio público. Solo las categorías <strong>activas</strong> se muestran.</p>
        </div>
      </div>

    </div>

  </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/instructions/categories/edit.js') }}"></script>
@endpush
