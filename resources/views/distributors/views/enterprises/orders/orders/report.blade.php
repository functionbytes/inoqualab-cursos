@extends('layouts.managers')


@section('page_header')
    @include('distributors.includes.card', ['title' => 'Reporte ordenes'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formReport" enctype="multipart/form-data" role="form" onSubmit="return false"
                  data-generate-url="{{ route('manager.orders.generate') }}">

                {{ csrf_field() }}

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Reporte ordenes</h6>
                        <p class="text-muted small mb-0">
                            Genera el reporte de órdenes filtrando por curso, empresa
                            y rango de fechas.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Cursos</label>
                                {!! Form::select('course', $courses, null , ['class' => 'select2 form-control' ,'name' => 'course', 'id' => 'course' ]) !!}
                                <label id="course-error" class="error d-none" for="course"></label>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Empresas</label>
                                {!! Form::select('enterprises', $enterprises, null , ['class' => 'select2 form-control' ,'name' => 'enterprises', 'id' => 'enterprises' ]) !!}
                                <label id="enterprises-error" class="error d-none" for="enterprises"></label>
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
                    <h6 class="mb-0 fw-bold">Sobre este reporte</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        El reporte se descarga con los filtros seleccionados: curso, empresa
                        y el rango de fechas indicado.
                    </p>
                </div>
            </div>
        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ asset('distributors/js/enterprises/orders/orders/report.js') }}"></script>
@endpush


