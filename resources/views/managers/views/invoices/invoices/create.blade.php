@extends('layouts.managers')

@section('content')

    <div class="row">
        <div class="col-lg-12 d-flex align-items-stretch">

            <div class="card w-100" id="invoices-create"
                 data-config='@php $__jsonInline1 = [
                    "routes" => [
                        "store" => route("manager.invoices.store"),
                        "view" => route("manager.invoices.view", ":id"),
                    ],
                 ]; @endphp@json($__jsonInline1)'>

                <form id="formInvoices" enctype="multipart/form-data" role="form">

                    {{ csrf_field() }}

                    <div class="card-body border-top">
                        <div class="d-flex no-block align-items-center">
                            <h5 class="mb-0">Editar factura</h5>

                        </div>
                        <p class="card-subtitle mb-3 mt-3">
                            Completa los datos para generar una nueva factura: distribuidor, condición y método de pago, y el rango de fechas correspondiente.
                        </p>

                        <div class="row">

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Distribuidor</label>
                                    <div class="input-group">
                                        {!! Form::select('distributor', $distributors, null , ['class' => 'select2 form-control' ,'name' => 'distributor', 'id' => 'distributor' ]) !!}
                                    </div>
                                    <label id="distributor-error" class="error d-none" for="distributor"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Condición pago</label>
                                    <div class="input-group">
                                        {!! Form::select('condition', $conditions, null , ['class' => 'select2 form-control' ,'name' => 'condition', 'id' => 'condition' ]) !!}
                                    </div>
                                    <label id="condition-error" class="error d-none" for="condition"></label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="control-label col-form-label">Metodo pago</label>
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


    <div id="view-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-danger"><i data-feather="x-octagon"></i></div>
                    <h4 class="my-0">¿Deseas visualizar la factura?</h4>
                    <p>Visualizaras el detalle de la factura que acabaste de genrar</p>
                    <div class="row justify-content-center mt-20  ">
                        <div class="col-sm-12 col-md-5">
                            <a href="" id="view-link" class="btn btn-danger w-100">Confirmar</a>
                        </div>
                        <div class="col-sm-12 col-md-5">
                            <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection



@push('scripts')
    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ asset('managers/js/views/invoices/invoices/create.js') }}"></script>
@endpush



