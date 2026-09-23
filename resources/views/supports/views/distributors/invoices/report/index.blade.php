@extends('layouts.managers')

@section('page_header')
    @include('supports.includes.card', ['title' => 'Reporte factura'])
@endsection

@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">
            <form id="formReport" enctype="multipart/form-data" role="form" onSubmit="return false"
                  data-generate-url="{{ route('support.distributors.invoices.generate') }}">

                {{ csrf_field() }}

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Reporte factura</h6>
                        <p class="text-muted small mb-0">
                            Genera el reporte de facturación de un distribuidor filtrando por condición,
                            tipo de factura y rango de fechas.
                        </p>
                    </div>

                    <div class="card-body">
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

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre este reporte</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        El rango de fechas es obligatorio. El reporte se descarga como archivo Excel (.xlsx)
                        con las facturas que coincidan con los filtros seleccionados.
                    </p>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ asset('supports/js/views/distributors/invoices/report/index.js') }}"></script>
@endpush
