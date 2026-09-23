@extends('layouts.managers')


@section('page_header')
    @include('enterprises.includes.card', ['title' => 'Visualizar estado curso'])
@endsection
@section('content')

<div class="row">
    <div class="col-lg-12 d-flex align-items-stretch">

        <div class="card w-100">

            <div class="card-header border-bottom">
                <h6 class="mb-1 fw-bold">Visualizar estado curso</h6>
                <p class="text-muted small mb-0">
                    Consulta el estado y las fechas de esta inscripción. Estos datos son de solo lectura.
                </p>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label fw-semibold">Curso</label>
                        <input type="text" class="form-control" id="course_title" name="course_title" value="{{ $course->title }}" disabled>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Fecha inicio</label>
                        <input type="text" class="form-control" id="enroll_start" name="enroll_start" value="{{ $inscription->enroll_start }}" disabled>
                    </div>

                    <div class="col-6">
                        <label class="form-label fw-semibold">Fecha limite</label>
                        <input type="text" class="form-control" id="enroll_expire" name="enroll_expire" value="{{ $inscription->enroll_expire  }}" disabled>
                    </div>

                    <div class="col-6">
                        <label class="form-label fw-semibold">Fecha culminado</label>
                        <input type="text" class="form-control" id="enroll_culminated" name="enroll_culminated" value="{{ $inscription->enroll_culminated != null ? $inscription->enroll_culminated : 'No culminado' }}" disabled>
                    </div>

                    <div class="col-6">
                        <label class="form-label fw-semibold">Estado curso</label>
                        <input type="text" class="form-control" id="course_status" name="course_status" value="{{ $inscription->culminated == 1 ? 'Finalizado' : 'Pendiente' }}" disabled>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

