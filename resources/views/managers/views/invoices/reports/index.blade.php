@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100" id="invoices-reports-index"
                 data-config='@json([
                    "routes" => [
                        "generate" => route("manager.invoices.generate"),
                    ],
                 ])'>

                <form id="formReport" enctype="multipart/form-data" role="form">

                    {{ csrf_field() }}

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0"> Reporte factura</h5>
                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Selecciona la empresa, la condición y el tipo de factura, junto con el rango de fechas, para generar el reporte de facturas.
                        </p>

                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Empresas</label>
                                    <div class="input-group">
                                        {!! Form::select('distributor', $distributors, null , ['class' => 'select2 form-control' ,'name' => 'distributor', 'id' => 'distributor' ]) !!}
                                    </div>
                                    <label id="distributor-error" class="error d-none" for="distributor"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Condición</label>
                                    <div class="input-group">
                                        {!! Form::select('condition', $conditions, null , ['class' => 'select2 form-control' ,'name' => 'condition', 'id' => 'condition' ]) !!}
                                    </div>
                                    <label id="condition-error" class="error d-none" for="condition"></label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="mb-3">
                                    <label  class="control-label col-form-label">Tipo de factura</label>
                                    <div class="input-group">
                                        {!! Form::select('method', $methods, null , ['class' => 'select2 form-control' ,'name' => 'method', 'id' => 'method' ]) !!}
                                    </div>
                                    <label id="method-error" class="error d-none" for="method"></label>
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

                            <div class="col-12">
                            <div class="border-top pt-1 mt-4">
                                <button type="submit" class="btn btn-info  px-4 waves-effect waves-light mt-2 w-100">
                                        Guardar
                                </button>
                            </div>
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
    <script src="{{ asset('managers/js/views/invoices/reports/index.js') }}"></script>
@endpush



