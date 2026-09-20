@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formAction" enctype="multipart/form-data" role="form"
                      data-action-url="{{ route('manager.enterprises.courses.action') }}"
                      data-redirect-url="{{ route('manager.users') }}"
                      data-enroll-start="{{ date('m/d/Y', strtotime($inscription->enroll_start)) }}"
                      data-enroll-expire="{{ date('m/d/Y', strtotime($inscription->enroll_expire)) }}">

                    {{ csrf_field() }}

                    <input name="inscription" id="inscription"  type="hidden" value="{{ $inscription->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Modificar fecha de curso</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Selecciona el nuevo rango de fechas de acceso al curso para este usuario.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Cliente</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control"  value="{{ Str::upper(Str::lower($user->firstname . ' ' . $user->lastname))  }}" placeholder="Ingresar celular" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Curso</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control"  value="{{ Str::upper(Str::lower($course->title))  }}" placeholder="Ingresar celular" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Fecha</label>
                                    <div class="input-group">
                                        <input type="text" id="range" name="range" class="form-control daterange" />
                                        <span class="input-group-text">
                                          <i class="fas fa-calendar fs-5"></i>
                                        </span>
                                    </div>
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
<script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('managers/js/views/enterprises/courses/action.js') }}"></script>
@endpush


