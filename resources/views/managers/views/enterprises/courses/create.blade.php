@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Asignar curso'])
@endsection
@section('content')

<div class="row g-4 align-items-start">

  {{-- Columna izquierda: formulario --}}
  <div class="col-lg-8">
    <form id="formCourse" enctype="multipart/form-data" role="form"
          data-store-url="{{ route('manager.enterprises.courses.store') }}"
          data-redirect-url="{{ route('manager.enterprises.courses', $enterprise->slack) }}">

      {{ csrf_field() }}

      <input id="enterprise" name="enterprise" type="hidden" value="{{ $enterprise->slack }}">

      <div class="card">

        <div class="card-header border-bottom">
          <h6 class="mb-1 fw-bold">Asignar curso</h6>
          <p class="text-muted small mb-0">
            Selecciona el curso que deseas asignar a los usuarios de esta empresa.
          </p>
        </div>

        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Empresa</label>
              <input type="text" class="form-control" value="{{ $enterprise->title }}" placeholder="Ingresar celular" disabled>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Cursos</label>
              {!! Form::select('course', $courses, null , ['class' => 'select2 form-control' ,'name' => 'course', 'id' => 'course' ]) !!}
              <label id="course-error" class="error d-none" for="course"></label>
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
        <h6 class="mb-0 fw-bold">Sobre esta asignación</h6>
      </div>
      <div class="card-body">
        <p class="text-muted mb-0">Solo los cursos asignados aquí estarán disponibles para matricular usuarios de esta empresa.</p>
      </div>
    </div>
  </div>

</div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/enterprises/courses/create.js') }}"></script>
@endpush
