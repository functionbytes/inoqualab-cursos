@extends('layouts.managers')


@section('page_header')
    @include('managers.includes.card', ['title' => 'Resumen ordenes'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <div id="orders-resumen-index"
                 data-config='@php $__jsonInline1 = [
                    "routes" => [
                        "getEnterprises" => route("manager.orders.resumen.get.enterprises"),
                        "generate" => route("manager.orders.resumen.generate"),
                    ],
                 ]; @endphp@json($__jsonInline1)'>

                <form id="formReport" enctype="multipart/form-data" role="form">

                    {{ csrf_field() }}

                    <div class="card">

                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Resumen ordenes</h6>
                        <p class="text-muted mb-3">
                            Selecciona el distribuidor, la empresa, la condición, el método de pago y el rango de fechas para generar el resumen de órdenes.
                        </p>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Distribuidor</label>
                                <div class="input-group">
                                    {!! Form::select('distributor', $distributors, null , ['class' => 'select2 form-control' ,'name' => 'distributor', 'id' => 'distributor' ]) !!}
                                </div>
                                <label id="distributor-error" class="error d-none" for="distributor"></label>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Empresas</label>
                                <div class="input-group">
                                    {!! Form::select('enterprise', [], null , ['class' => 'select2 form-control' ,'name' => 'enterprise', 'id' => 'enterprise' ]) !!}
                                </div>
                                <label id="enterprise-error" class="error d-none" for="enterprise"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Condición</label>
                                <div class="input-group">
                                    {!! Form::select('condition', $conditions, null , ['class' => 'select2 form-control' ,'name' => 'condition', 'id' => 'condition' ]) !!}
                                </div>
                                <label id="condition-error" class="error d-none" for="condition"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Metodo de pago</label>
                                <div class="input-group">
                                    {!! Form::select('method', $methods, null , ['class' => 'select2 form-control' ,'name' => 'method', 'id' => 'method' ]) !!}
                                </div>
                                <label id="method-error" class="error d-none" for="method"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Tipo de factura</label>
                                <div class="input-group">
                                    {!! Form::select('type', $types, null , ['class' => 'select2 form-control' ,'name' => 'type', 'id' => 'type' ]) !!}
                                </div>
                                <label id="type-error" class="error d-none" for="type"></label>
                            </div>
                            <div class="col-6">
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
                    <h6 class="mb-0 fw-bold">Sobre este resumen</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Deja un filtro sin seleccionar para incluir todas sus opciones en el resumen. Si eliges un distribuidor, la lista de empresas se filtra automáticamente a las suyas.</p>
                </div>
            </div>

        </div>

    </div>

@endsection



@push('scripts')
    <script src="{{ url('managers/libs/daterangepicker/daterangepicker.js') }}" type="text/javascript"></script>
    <script src="{{ asset('managers/js/views/orders/resumen/index.js') }}"></script>
@endpush


