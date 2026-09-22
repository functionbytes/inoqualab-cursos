@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Reporte factura'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <div id="invoices-reports-index"
                 data-config='@json([
                    "routes" => [
                        "generate" => route("manager.invoices.generate"),
                    ],
                 ])'>

                <form id="formReport" enctype="multipart/form-data" role="form">

                    {{ csrf_field() }}

                    <div class="card">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Reporte factura</h6>
                        <p class="text-muted mb-3">
                            Selecciona la empresa, la condición y el tipo de factura, junto con el rango de fechas, para generar el reporte de facturas.
                        </p>

                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label fw-semibold">Empresas</label>
                                <div class="input-group">
                                    {!! Form::select('distributor', $distributors, null , ['class' => 'select2 form-control' ,'name' => 'distributor', 'id' => 'distributor' ]) !!}
                                </div>
                                <label id="distributor-error" class="error d-none" for="distributor"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Condición</label>
                                <div class="input-group">
                                    {!! Form::select('condition', $conditions, null , ['class' => 'select2 form-control' ,'name' => 'condition', 'id' => 'condition' ]) !!}
                                </div>
                                <label id="condition-error" class="error d-none" for="condition"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Tipo de factura</label>
                                <div class="input-group">
                                    {!! Form::select('method', $methods, null , ['class' => 'select2 form-control' ,'name' => 'method', 'id' => 'method' ]) !!}
                                </div>
                                <label id="method-error" class="error d-none" for="method"></label>
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
        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre este reporte</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Deja un filtro sin seleccionar para incluir todas sus opciones en el reporte.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ asset('managers/js/views/invoices/reports/index.js') }}"></script>
@endpush



