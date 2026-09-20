@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formReport" enctype="multipart/form-data" role="form"
                      data-reasign-url="{{ route('manager.enterprises.action.reasign') }}"
                      data-redirect-url="{{ route('manager.enterprises.courses.view', [$enterprise->slack, $course->slack]) }}">

                    {{ csrf_field() }}

                    <input  id="old" name="old" type="hidden" value="{{ $course->slack }}">
                    <input  id="enterprise" name="enterprise" type="hidden" value="{{ $enterprise->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Reasignar usuarios</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Selecciona el curso destino y los usuarios que deseas reasignar.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Curso</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control"  value="{{ $course->title }}" placeholder="Ingresar celular" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Curso a reacionar</label>
                                    <div class="input-group">
                                        {!! Form::select('course', $courses, null , ['class' => 'select2 form-control' ,'name' => 'course', 'id' => 'course' ]) !!}
                                    </div>
                                    <label id="course-error" class="error d-none" for="course"></label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Usuarios</label>
                                    <div class="input-group">
                                        {!! Form::select('user[]', $users, null , ['class' => 'select2 form-control' ,'name' => 'user', 'id' => 'user' ]) !!}
                                    </div>
                                    <label id="user-error" class="error d-none" for="user"></label>
                                </div>
                            </div>
                        </div>

                    </div>

                     <div class="col-12"><div class="action-form border-top mt-4">
                        <div class="text-center">
                            <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                Guardar
                            </button>
                        </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
<script src="{{ asset('managers/js/views/enterprises/courses/reasign.js') }}"></script>
@endpush



