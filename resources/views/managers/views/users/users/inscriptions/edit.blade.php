@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Modificar fecha de curso'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <div class="card" id="users-inscriptions-edit"
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

                    <input name="inscription" id="inscription" type="hidden" value="{{ $inscription->slack }}">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Modificar fecha de curso</h6>
                        <p class="text-muted small mb-0">
                            Modifica la fecha del curso asignado a esta inscripción. El cambio se aplicará al guardar.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Cliente</label>
                                <input type="text" class="form-control" value="{{ Str::upper(Str::lower($user->firstname . ' ' . $user->lastname))  }}" placeholder="Ingresar celular" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Curso</label>
                                <input type="text" class="form-control" value="{{ Str::upper(Str::lower($course->title))  }}" placeholder="Ingresar celular" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Fecha</label>
                                <div class="input-group">
                                    <input type="text" id="range" name="range" class="form-control daterange" />
                                    <span class="input-group-text">
                                      <i class="fas fa-calendar fs-5"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre este cambio</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">La nueva fecha reemplaza el vencimiento actual del acceso de este cliente al curso.</p>
                </div>
            </div>
        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ asset('managers/js/views/users/users/inscriptions/edit.js') }}"></script>
@endpush


