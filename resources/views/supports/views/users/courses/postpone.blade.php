@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', ['title' => 'Posponer fecha - ' . $course->title])
@endsection

@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formAction" enctype="multipart/form-data" role="form" onSubmit="return false"
                  data-action-url="{{ route('support.users.inscriptions.action') }}"
                  data-redirect-url="{{ route('support.users.courses.index', $user->slack) }}">

                {{ csrf_field() }}

                <input name="inscription" id="inscription" type="hidden" value="{{ $inscription->slack }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Modificar fecha de curso</h6>
                        <p class="text-muted small mb-0">
                            Actualiza el rango de fechas de la inscripción. Los cambios se guardarán al hacer clic en Guardar.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Cliente</label>
                                <input type="text" class="form-control" value="{{ Str::upper(Str::lower($user->firstname . ' ' . $user->lastname)) }}" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Curso</label>
                                <input type="text" class="form-control" value="{{ Str::upper(Str::lower($course->title)) }}" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Fecha</label>
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
                    <h6 class="mb-0 fw-bold">Sobre esta fecha</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        El rango de fechas define cuándo el curso queda disponible para el estudiante
                        en su panel.
                    </p>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ asset('supports/js/views/users/courses/postpone.js') }}"></script>
@endpush
