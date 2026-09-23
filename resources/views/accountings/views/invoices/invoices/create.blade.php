@extends('layouts.managers')


@section('page_header')
    @include('accountings.includes.card', ['title' => 'Crear factura'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formInvoices" enctype="multipart/form-data" role="form"
                  data-store-url="{{ route('accounting.invoices.store') }}"
                  data-view-url-template="{{ route('accounting.invoices.view', ':id') }}">

                {{ csrf_field() }}

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Crear factura</h6>
                        <p class="text-muted small mb-0">
                            Completa los datos para generar una nueva factura: distribuidor, condición y método de pago, y el rango de fechas correspondiente.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label fw-semibold">Distribuidor</label>
                                <div class="input-group">
                                    {!! Form::select('distributor', $distributors, null , ['class' => 'select2 form-control' ,'name' => 'distributor', 'id' => 'distributor' ]) !!}
                                </div>
                                <label id="distributor-error" class="error d-none" for="distributor"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Estado de factura</label>
                                <div class="input-group">
                                    {!! Form::select('condition', $conditions, null , ['class' => 'select2 form-control' ,'name' => 'condition', 'id' => 'condition' ]) !!}
                                </div>
                                <label id="condition-error" class="error d-none" for="condition"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Metodo pago</label>
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
                    <h6 class="mb-0 fw-bold">Sobre esta factura</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Se generará una factura con las órdenes del distribuidor que coincidan con la condición, el método de pago y el rango de fechas seleccionados.</p>
                </div>
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
                    <div class="display-4 text-danger"><i class="fas fa-circle-xmark"></i></div>
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
    <script src="{{ asset('accountings/js/views/invoices/invoices/create.js') }}"></script>
@endpush


