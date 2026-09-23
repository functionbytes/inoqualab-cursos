@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Modificar fecha de curso'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formAction" enctype="multipart/form-data" role="form"
                  data-update-url="{{ route('manager.enterprises.courses.users') }}"
                  data-enroll-start="{{ date('m/d/Y', strtotime($inscription->enroll_start)) }}"
                  data-enroll-expire="{{ date('m/d/Y', strtotime($inscription->enroll_expire)) }}">

                {{ csrf_field() }}

                <input name="order" id="order" type="hidden" value="{{ $order->slack }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Modificar fecha de curso</h6>
                        <p class="text-muted small mb-0">
                            Selecciona la nueva fecha de vencimiento del acceso al curso.
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

                </div>
            </form>
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
<script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('managers/js/views/enterprises/courses/postpone.js') }}"></script>
@endpush



