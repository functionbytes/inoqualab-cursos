@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', ['title' => 'Posponer fecha - ' . $course->title])
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100">

                <form id="formAction" enctype="multipart/form-data" role="form" onSubmit="return false"
                      data-action-url="{{ route('support.users.inscriptions.action') }}"
                      data-redirect-url="{{ route('support.users.courses.index', $user->slack) }}">

                    {{ csrf_field() }}

                    <input name="inscription" id="inscription" type="hidden" value="{{ $inscription->slack }}">

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Modificar fecha de curso</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Actualiza el rango de fechas de la inscripción. Los cambios se guardarán al hacer clic en Guardar.
                        </p>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Cliente</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{ Str::upper(Str::lower($user->firstname . ' ' . $user->lastname)) }}" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Curso</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{ Str::upper(Str::lower($course->title)) }}" disabled>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Fecha</label>
                                    <div class="input-group">
                                        <input type="text" id="range" name="range" class="form-control daterange"
                                               data-start="{{ date('d/m/Y', strtotime($inscription->enroll_start)) }}"
                                               data-end="{{ date('d/m/Y', strtotime($inscription->enroll_expire)) }}" />
                                        <span class="input-group-text">
                                          <i class="fas fa-calendar fs-5"></i>
                                        </span>
                                    </div>
                                    <label id="range-error" class="error d-none" for="range"></label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="action-form border-top mt-4 text-center">
                                <button type="submit" class="btn btn-info px-4 waves-effect waves-light mt-2 w-100">
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
    <script src="{{ asset('supports/js/views/users/courses/postpone.js') }}"></script>
@endpush
