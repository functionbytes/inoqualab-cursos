@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', ['title' => 'Asignacion cursos'])
@endsection

@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formCourses" enctype="multipart/form-data" role="form" onSubmit="return false"
                  data-update-url="{{ route('support.distributors.courses.update') }}"
                  data-redirect-url-template="{{ route('support.distributors.navegation', ':slack') }}">

                {{ csrf_field() }}

                <input id="slack" name="slack" type="hidden" value="{{ $distributor->slack }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Asignacion cursos</h6>
                        <p class="text-muted small mb-0">
                            Selecciona los cursos que este distribuidor tendrá disponibles para
                            asignar a sus empresas.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Cursos</label>
                                <div class="input-group">
                                    {!! Form::select('courses', $courses, $course , ['class' => 'select2 form-control' , 'multiple' => 'multiple' ,'name' => 'courses', 'id' => 'courses' ]) !!}
                                </div>
                                <label id="courses-error" class="error d-none" for="courses"></label>
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
                    <h6 class="mb-0 fw-bold">Sobre la asignación</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        Los cursos que el distribuidor ya tiene asignados aparecen preseleccionados;
                        solo quedan asignados los que estén marcados al guardar.
                    </p>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('supports/js/views/distributors/courses/index.js') }}"></script>
@endpush
