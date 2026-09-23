@extends('layouts.managers')


@section('page_header')
    @include('supports.includes.card', ['title' => 'Crear categoria'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">

      <form id="formCategories" enctype="multipart/form-data" role="form"
            data-store-url="{{ route('support.faqs.categories.store') }}"
            data-redirect-url="{{ route('support.faqs.categories') }}">

        {{ csrf_field() }}

        <div class="card">

          <div class="card-header border-bottom">
              <h6 class="mb-1 fw-bold">Crear categoria</h6>
              <p class="text-muted small mb-0">
                  Completa el título de la categoría. Los campos marcados como obligatorios deben diligenciarse para poder guardarla.
              </p>
          </div>

          <div class="card-body">
            <div class="row g-3">

              <div class="col-6">
                <label class="form-label fw-semibold">Titulo</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="Ingresa titulo">
              </div>

              <div class="col-6">
                <label class="form-label fw-semibold">Estado</label>
                <div class="input-group">
                  {!! Form::select('available', $availables, null , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
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
          <p class="text-muted mb-0">Las categorías agrupan las preguntas frecuentes en el centro de ayuda. Solo las categorías con <strong>Estado</strong> habilitado son visibles.</p>
        </div>
      </div>

    </div>

  </div>

@endsection



@push('scripts')
<script src="{{ asset('supports/js/views/faqs/categories/create.js') }}"></script>
@endpush

