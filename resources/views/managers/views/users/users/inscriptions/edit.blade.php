@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Modificar fecha de curso'])
@endsection
@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100" id="users-inscriptions-edit"
                 @php
                    $__jsonInline1 = [
                        "enrollStart" => date("d/m/Y", strtotime($inscription->enroll_start)),
                        "enrollExpire" => date("d/m/Y", strtotime($inscription->enroll_expire)),
                        "routes" => [
                            "action" => route("manager.users.inscriptions.action"),
                            "back" => route("manager.users.inscriptions", $user->slack),
                        ],
                    ];
                 @endphp
                 data-config='@json($__jsonInline1)'>

                <form id="formAction" enctype="multipart/form-data" role="form">

                    {{ csrf_field() }}

                    <input name="inscription" id="inscription"  type="hidden" value="{{ $inscription->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Modificar fecha de curso</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Modifica la fecha del curso asignado a esta inscripción. El cambio se aplicará al guardar.
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
                        <div class="col-12">
                            <div class="action-form border-top mt-4 text-center">
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
    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ asset('managers/js/views/users/users/inscriptions/edit.js') }}"></script>
@endpush


