@extends('layouts.managers')


@section('page_header')
    @include('enterprises.includes.card', ['title' => 'Reporte — ' . $course->title])
@endsection
@section('content')


    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formReport" role="form" onSubmit="return false"
                  data-generate-url="{{ route('enterprise.courses.generate') }}">
                {{ csrf_field() }}
                <input type="hidden" id="enterprise_id" name="enterprise" value="{{ $enterprises->id ?? '' }}">
                <input type="hidden" id="course_id" name="course" value="{{ $course->id }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Reporte — {{ $course->title }}</h6>
                        <p class="text-muted small mb-0">
                            Selecciona la modalidad y descarga el reporte de participantes en Excel.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Modalidad</label>
                                {!! Form::select('modalitie', $listmodalities, null, ['class' => 'select2 form-control', 'id' => 'modalitie']) !!}
                                <label id="modalitie-error" class="error d-none" for="modalitie"></label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Descargar reporte
                        </button>
                    </div>

                </div>
            </form>
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre este reporte</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">El reporte se descarga en Excel con los participantes de este curso según la modalidad seleccionada.</p>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script src="{{ asset('enterprises/js/views/report/index.js') }}"></script>
@endpush
