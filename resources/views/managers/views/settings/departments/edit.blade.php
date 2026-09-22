@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Editar departamento'])
@endsection
@section('content')

  <div class="row g-4 align-items-start">

    {{-- Columna izquierda: formulario --}}
    <div class="col-lg-8">
          <form id="formDepartment" enctype="multipart/form-data" role="form"
                data-urls='@php $__jsonInline1 = [
                    "update" => route("manager.departments.update"),
                    "index" => route("manager.departments"),
                ]; @endphp@json($__jsonInline1)'>

                {{ csrf_field() }}

                <input type="hidden" id="slack" name="slack" value="{{ $department->slack }}">

                <div class="card">

                  <div class="card-body">
                    <h6 class="fw-bold text-dark mb-1">Editar departamento</h6>
                    <p class="text-muted mb-3">
                      Actualiza los datos del departamento. Los cambios se guardarán al hacer clic en Guardar.
                    </p>

                    <div class="row g-3">
                      <div class="col-6">
                        <label class="form-label fw-semibold">Titulo</label>
                        <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ $department->title  }}" >
                      </div>
                      <div class="col-6">
                        <label class="form-label fw-semibold">Estado</label>
                        <div class="input-group">
                          {!! Form::select('available', $availables, $department->available , ['class' => 'select2 form-control' ,'name' => 'available', 'id' => 'available' ]) !!}
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
          <h6 class="mb-0 fw-bold">Sobre los departamentos</h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-0">Solo los departamentos con estado <strong>activo</strong> quedan disponibles para asignación en el resto del sistema.</p>
        </div>
      </div>

    </div>

  </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/settings/departments/edit.js') }}"></script>
@endpush
