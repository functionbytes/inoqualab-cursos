@extends('layouts.managers')


@section('page_header')
    @include('accountings.includes.card', ['title' => 'Editar orden'])
@endsection
@section('content')

    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formulario --}}
        <div class="col-lg-8">

            <form id="formOrders" enctype="multipart/form-data" role="form"
                  data-update-url="{{ route('accounting.orders.update') }}"
                  data-view-url-template="{{ route('accounting.orders.view', ':id') }}"
                  data-payment-date="{{ \Carbon\Carbon::parse($order->payment_at)->format('Y-m-d') }}">

                {{ csrf_field() }}

                <input type="hidden" id="slack" name="slack" value="{{ $order->slack }}">

                <div class="card">

                    <div class="card-header border-bottom">
                        <h6 class="mb-1 fw-bold">Editar orden</h6>
                        <p class="text-muted small mb-0">
                            Actualiza los datos de la orden. Los cambios se guardarán al hacer clic en Guardar.
                        </p>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-6">
                                <label class="form-label fw-semibold">Orden codigo</label>
                                <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ $order->slack  }}" disabled>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Orden referencia</label>
                                <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ $order->reference  }}" disabled>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Condición pago</label>
                                <div class="input-group">
                                    {!! Form::select('condition', $conditions, $order->condition_id , ['class' => 'select2 form-control' ,'name' => 'condition', 'id' => 'condition' ]) !!}
                                </div>
                                <label id="condition-error" class="error d-none" for="condition"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Metodo pago</label>
                                <div class="input-group">
                                    {!! Form::select('method', $methods, $order->method_id , ['class' => 'select2 form-control' ,'name' => 'method', 'id' => 'method' ]) !!}
                                </div>
                                <label id="method-error" class="error d-none" for="method"></label>
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Fecha pago</label>
                                <input type="text" class="form-control datepicker" id="payment"  name="payment"  data-date-format="yyyy-mm-dd"  value="" >
                            </div>

                            <div class="col-6">
                                <label class="form-label fw-semibold">Fecha creación</label>
                                <input type="text" class="form-control" id="title"  name="title"  placeholder="Ingresa titulo" value=" {{ date('Y-m-d', strtotime($order->created_at)) }}" disabled>
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
                    <h6 class="mb-0 fw-bold">Sobre esta orden</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">El código y la referencia son generados por el sistema y no se pueden modificar. Cambia la condición, el método de pago y la fecha de pago según corresponda.</p>
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
                        <h4 class="my-0">¿Deseas visualizar la orden?</h4>
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
    <script src="{{ url('managers/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('accountings/js/views/orders/orders/edit.js') }}"></script>
@endpush


