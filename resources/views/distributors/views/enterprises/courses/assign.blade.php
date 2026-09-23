@extends('layouts.managers')


@section('page_header')
    @include('distributors.includes.card', ['title' => 'Asignacion cursos'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formCourses" enctype="multipart/form-data" role="form" onSubmit="return false"
                  data-update-url="{{ route('distributor.enterprises.courses.assign.update') }}"
                  data-redirect-url="{{ route('distributor.enterprises.courses', $enterprise->slack) }}">

                {{ csrf_field() }}

                <input  id="slack" name="slack" type="hidden" value="{{ $enterprise->slack }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Asignacion cursos</h6>
                        <p class="text-muted small mb-0">
                            Selecciona los cursos que esta empresa tendrá disponibles para matricular
                            a sus usuarios.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Cursos</label>
                                {!! Form::select('courses', $courses, $course , ['class' => 'select2 form-control' , 'multiple' => 'multiple' ,'name' => 'courses', 'id' => 'courses' ]) !!}
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
                        Solo los cursos seleccionados aquí estarán disponibles para matricular
                        usuarios de esta empresa.
                    </p>
                </div>
            </div>
        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ asset('distributors/js/enterprises/courses/assign.js') }}"></script>
@endpush


