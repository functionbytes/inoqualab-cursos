@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Insertar usuarios'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formInclide" enctype="multipart/form-data" role="form"
                  data-include-url="{{ route('manager.enterprises.courses.include') }}"
                  data-redirect-url="{{ route('manager.enterprises.courses.view', [$enterprise->slack, $course->slack]) }}">

                {{ csrf_field() }}

                <input id="course" name="course" type="hidden" value="{{ $course->slack }}">
                <input id="enterprise" name="enterprise" type="hidden" value="{{ $enterprise->slack }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Insertar usuarios</h6>
                        <p class="text-muted small mb-0">
                            Selecciona los usuarios que deseas inscribir en este curso.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Curso</label>
                                <input type="text" class="form-control" value="{{ $course->title }}" placeholder="Ingresar celular" disabled>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Usuarios</label>
                                {!! Form::select('user[]', $users, null , ['class' => 'select2 form-control' ,'name' => 'users', 'id' => 'users' , 'multiple' => 'multiple']) !!}
                                <label id="users-error" class="error d-none" for="users"></label>
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
                    <h6 class="mb-0 fw-bold">Sobre esta inscripción</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Los usuarios seleccionados quedarán inscritos en este curso y podrán acceder a su contenido de inmediato.</p>
                </div>
            </div>
        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/enterprises/courses/insert.js') }}"></script>
@endpush



