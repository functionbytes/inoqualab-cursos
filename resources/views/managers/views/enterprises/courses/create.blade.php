@extends('layouts.managers')

@section('content')

<div class="row">
  <div class="col-lg-12 d-flex align-items-stretch">
    
    <div class="card w-100">

      <form id="formCourse" enctype="multipart/form-data" role="form"
            data-store-url="{{ route('manager.enterprises.courses.store') }}"
            data-redirect-url="{{ route('manager.enterprises.courses', $enterprise->slack) }}">

        {{ csrf_field() }}

        <input id="enterprise" name="enterprise" type="hidden" value="{{ $enterprise->slack }}">

        <div class="card-body border-top">
          <div class="d-flex no-block align-items-center">
            <h5 class="mb-0"> Asignar curso</h5>

          </div>
          <p class="card-subtitle mb-3 mt-3">
            Selecciona el curso que deseas asignar a los usuarios de esta empresa.
          </p>

          <div class="row">
            <div class="col-12">
              <div class="mb-3">
                <label  class="control-label col-form-label">Empresa</label>
                <div class="input-group">
                  <input type="text" class="form-control" value="{{ $enterprise->title }}" placeholder="Ingresar celular"
                    disabled>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="mb-3">
                <label  class="control-label col-form-label">Cursos</label>
                <div class="input-group">
                  {!! Form::select('course', $courses, null , ['class' => 'select2 form-control' ,'name' => 'course',
                  'id' => 'course' ]) !!}
                </div>
                <label id="course-error" class="error d-none" for="course"></label>
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
<script src="{{ asset('managers/js/views/enterprises/courses/create.js') }}"></script>
@endpush
