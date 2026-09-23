@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Reasignar usuarios'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formReport" enctype="multipart/form-data" role="form"
                  data-reasign-url="{{ route('manager.enterprises.action.reasign') }}"
                  data-redirect-url="{{ route('manager.enterprises.courses.view', [$enterprise->slack, $course->slack]) }}">

                {{ csrf_field() }}

                <input id="old" name="old" type="hidden" value="{{ $course->slack }}">
                <input id="enterprise" name="enterprise" type="hidden" value="{{ $enterprise->slack }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Reasignar usuarios</h6>
                        <p class="text-muted small mb-0">
                            Selecciona el curso destino y los usuarios que deseas reasignar.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Curso</label>
                                <input type="text" class="form-control" value="{{ $course->title }}" placeholder="Ingresar celular" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Curso a reasignar</label>
                                {!! Form::select('course', $courses, null , ['class' => 'select2 form-control' ,'name' => 'course', 'id' => 'course' ]) !!}
                                <label id="course-error" class="error d-none" for="course"></label>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Usuarios</label>
                                {!! Form::select('user[]', $users, null , ['class' => 'select2 form-control' ,'name' => 'user', 'id' => 'user' ]) !!}
                                <label id="user-error" class="error d-none" for="user"></label>
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
                    <h6 class="mb-0 fw-bold">Sobre esta reasignación</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Los usuarios seleccionados se trasladan del curso actual al curso destino elegido.</p>
                </div>
            </div>
        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/enterprises/courses/reasign.js') }}"></script>
@endpush



