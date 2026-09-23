@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', ['title' => 'Inscripciones'])
@endsection

@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formInscriptions" enctype="multipart/form-data" role="form"
                  data-enroll-url="{{ route('support.enterprises.inscriptions.enroll') }}"
                  data-store-url="{{ route('support.enterprises.inscriptions.store') }}">

                <input type="hidden" name="enterprise" id="enterprise" value="{{ $enterprise->slack}}">

                {{ csrf_field() }}

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Inscripciones</h6>
                        <p class="text-muted small mb-0">
                            Matricula a un usuario de esta empresa en uno de sus cursos disponibles.
                            Selecciona el curso y el usuario a inscribir.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Cursos</label>
                                <div class="input-group">
                                    {!! Form::select('course',$courses, null , ['class' => 'select2 form-control' ,'name' => 'course', 'id' => 'course']) !!}
                                </div>
                                <label id="course-error" class="error d-none" for="course"></label>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Usuarios</label>
                                <div class="input-group">
                                    {!! Form::select('user',$users, null , ['class' => 'select2 form-control' ,'name' => 'user', 'id' => 'user']) !!}
                                </div>
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
                    <h6 class="mb-0 fw-bold">Sobre las inscripciones</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        Si el usuario ya está inscrito en el curso seleccionado, el sistema lo avisará y te
                        dará la opción de inscribirlo nuevamente.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <div id="error-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-danger"><i class="fas fa-circle-xmark"></i></div>
                    <h4 class="my-0">Este usuario ya esta inscrito a este curso</h4>
                    <p><span class="course"> <span> con fecha <span class="start"> <span></span>
                    <div class="row justify-content-center mt-20  ">
                        <div class="col-sm-12 col-md-5 w-100">
                            <a class="btn btn-primary w-100 registration" data-course="" data-user="" data-enterprise="" >Inscribir nuevamente</a>
                            <button type="button" class="btn btn-primary w-100 mt-1 registration-close" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('supports/js/enterprises/enterprises/inscriptions/index.js') }}"></script>
@endpush
